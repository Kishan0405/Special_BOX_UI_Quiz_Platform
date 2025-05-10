<?php
// admin/generate_content_api_add_question.php

// Strict types and error reporting
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
// ini_set('error_log', '/path/to/your/php-error.log');

require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';

// --- Constants ---
define('GEMINI_API_ENDPOINT_BASE', 'https://generativelanguage.googleapis.com/v1beta/models/');
define('GEMINI_MODEL', 'gemini-2.0-flash');
define('DEFAULT_GENERATION_TARGET', 'generic');
define('CURL_TIMEOUT_SECONDS', 45);
define('CURL_CONNECTTIMEOUT_SECONDS', 10);

// --- Security: Authentication & Authorization ---
requireLogin();

// --- Security: Role Check ---
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'user'])) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode(['error' => true, 'message' => 'Permission denied.']);
    exit;
}

// --- Get API Key Securely ---
$apiKey = 'AIzaSyDiD7MRHVtINKL64sRodLTP9LDQfJ4FWmY'; // Replace with environment variable in production
if (empty($apiKey)) {
    http_response_code(500);
    error_log("FATAL ERROR: Gemini API Key is not configured.");
    header('Content-Type: application/json');
    echo json_encode(['error' => true, 'message' => 'AI service configuration error.']);
    exit;
}

// --- Request Validation ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => true, 'message' => 'Invalid request method. Only POST is accepted.']);
    exit;
}

// Get and decode JSON payload
$jsonPayload = file_get_contents('php://input');
$requestData = json_decode($jsonPayload, true);

// Validate JSON and 'prompt'
if (json_last_error() !== JSON_ERROR_NONE || !isset($requestData['prompt'])) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => true, 'message' => 'Invalid JSON payload or missing prompt.']);
    exit;
}

// Sanitize 'prompt'
$userPrompt = trim((string)$requestData['prompt']);
if (empty($userPrompt)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['error' => true, 'message' => 'Prompt cannot be empty.']);
    exit;
}

// Validate 'target' field
$targetField = isset($requestData['target']) ? trim((string)$requestData['target']) : DEFAULT_GENERATION_TARGET;
$allowedTargets = ['question', 'options', 'explanation', 'hint', 'tags', 'generic'];
if (!in_array($targetField, $allowedTargets)) {
    $targetField = DEFAULT_GENERATION_TARGET;
}

// Get 'question' field if provided
$providedQuestion = isset($requestData['question']) ? trim((string)$requestData['question']) : null;

// Use question if provided, otherwise fallback to prompt for options, explanation, hint, tags
$inputText = $providedQuestion ?: $userPrompt;

// --- Prepare Prompt for Gemini ---
$systemInstructions = [
    'generic' => "You are 'Special BOX AI', specialized in generating content for online quizzes. Be concise, engaging, and directly address the user's request.",
    'question' => "You are an expert in crafting clear, concise, and relevant quiz questions for educational purposes.",
    'options' => "You are skilled at generating four distinct, plausible answer options for a quiz question, with exactly one correct answer.",
    'explanation' => "You are adept at writing brief, informative explanations for why the correct answer is correct for a quiz question.",
    'hint' => "You are skilled at creating subtle, helpful hints for quiz questions without revealing the answer.",
    'tags' => "You are an expert at generating relevant, comma-separated tags for quiz questions based on the topic.",
];

$systemInstruction = $systemInstructions[$targetField] ?? $systemInstructions['generic'];
$fullPrompt = "";
$maxTokens = 256;

switch ($targetField) {
    case 'question':
        $fullPrompt = $systemInstruction . "\n\nGenerate ONE clear and concise quiz question (max 50 words) based on this topic: \"{$userPrompt}\"\n\nOutput only the question text, nothing else.";
        $maxTokens = 100;
        break;
    case 'options':
        $fullPrompt = $systemInstruction . "\n\nGenerate exactly FOUR answer options for this quiz question: \"{$inputText}\". Exactly one option must be correct, and the others plausible but incorrect. Output as a valid JSON array of objects, each with 'text' (string) and 'is_correct' (boolean) fields. Example: [{\"text\": \"Option 1\", \"is_correct\": true}, {\"text\": \"Option 2\", \"is_correct\": false}, {\"text\": \"Option 3\", \"is_correct\": false}, {\"text\": \"Option 4\", \"is_correct\": false}].\n\nOutput only the JSON array, without markdown or extra text.";
        $maxTokens = 200;
        break;
    case 'explanation':
        $fullPrompt = $systemInstruction . "\n\nGenerate a brief explanation (2-3 sentences, max 75 words) for why the correct answer is correct for this quiz question: \"{$inputText}\"\n\nOutput only the explanation text.";
        $maxTokens = 150;
        break;
    case 'hint':
        $fullPrompt = $systemInstruction . "\n\nGenerate a subtle, helpful hint (1-2 sentences, max 50 words) for this quiz question: \"{$inputText}\" without revealing the answer.\n\nOutput only the hint text.";
        $maxTokens = 100;
        break;
    case 'tags':
        $fullPrompt = $systemInstruction . "\n\nGenerate 3-5 relevant, comma-separated tags for this quiz question: \"{$inputText}\". Example: 'math, algebra, equations'\n\nOutput only the tags.";
        $maxTokens = 50;
        break;
    default:
        $fullPrompt = $systemInstruction . "\n\nGenerate relevant quiz content based on: \"{$userPrompt}\"\n\nKeep the response concise.";
        $maxTokens = 512;
}

// --- Prepare API Request Data ---
$apiUrl = GEMINI_API_ENDPOINT_BASE . GEMINI_MODEL . ':generateContent?key=' . $apiKey;

$data = [
    'contents' => [
        [
            'parts' => [
                ['text' => $fullPrompt]
            ]
        ]
    ],
    'generationConfig' => [
        'temperature' => 0.7,
        'topK' => 40,
        'topP' => 0.95,
        'maxOutputTokens' => $maxTokens,
    ],
    'safetySettings' => [
        ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
        ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
        ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
        ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'],
    ],
];

// --- Helper Function for API Call ---
function callGeminiApi(string $url, array $payload): array {
    $jsonData = json_encode($payload);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['success' => false, 'data' => null, 'error' => 'Failed to encode API request: ' . json_last_error_msg(), 'httpCode' => 500];
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($jsonData)
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, CURL_TIMEOUT_SECONDS);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, CURL_CONNECTTIMEOUT_SECONDS);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErrorNo = curl_errno($ch);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlErrorNo !== CURLE_OK) {
        error_log("cURL Error: (#{$curlErrorNo}) {$curlError} - URL: {$url}");
        return ['success' => false, 'data' => null, 'error' => "Failed to connect to AI service: {$curlError}", 'httpCode' => 500];
    }

    $responseData = json_decode((string)$response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Non-JSON Response: HTTP {$httpCode} - Response: " . substr((string)$response, 0, 500));
        return ['success' => false, 'data' => null, 'error' => 'Invalid response format from AI service.', 'httpCode' => $httpCode];
    }

    if ($httpCode >= 400 || isset($responseData['error'])) {
        $apiErrorMessage = $responseData['error']['message'] ?? 'Unknown API error';
        error_log("API Error: HTTP {$httpCode} - Message: {$apiErrorMessage}");
        return ['success' => false, 'data' => $responseData, 'error' => "AI service error: {$apiErrorMessage}", 'httpCode' => $httpCode];
    }

    if (!isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
        $blockReason = $responseData['promptFeedback']['blockReason'] ?? 'Unknown reason';
        $userMessage = $blockReason === 'SAFETY' ? 'Content blocked due to safety filters. Please modify your prompt.' : 'Content generation failed: ' . str_replace('_', ' ', $blockReason);
        error_log("No content generated. Block Reason: {$blockReason}");
        return ['success' => false, 'data' => $responseData, 'error' => $userMessage, 'httpCode' => 400];
    }

    return ['success' => true, 'data' => $responseData, 'error' => null, 'httpCode' => $httpCode];
}

// --- Handle Options Fallback Parsing ---
function parseOptionsFallback(string $text): ?array {
    // Clean up common issues
    $text = trim($text);
    // Remove markdown code blocks
    $text = preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $text);
    // Try JSON first
    $options = json_decode($text, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($options) && count($options) === 4 && allOptionsHaveTextAndIsCorrect($options)) {
        return $options;
    }

    // Fallback: Parse plain text (e.g., numbered or bulleted list)
    $lines = array_filter(array_map('trim', explode("\n", $text)), fn($line) => !empty($line));
    if (count($lines) >= 4) {
        $options = [];
        $correctIndex = null;
        foreach (array_slice($lines, 0, 4) as $index => $line) {
            // Remove leading numbers, bullets, or asterisks
            $cleanLine = preg_replace('/^\d+\.\s*|\*\s*|-+\s*/', '', $line);
            // Check for correct answer indicator (e.g., "(correct)", "[correct]")
            $isCorrect = preg_match('/\(correct\)|\[correct\]/i', $cleanLine);
            if ($isCorrect) {
                $correctIndex = $index;
                $cleanLine = preg_replace('/\(correct\)|\[correct\]/i', '', $cleanLine);
            }
            $options[] = [
                'text' => trim($cleanLine),
                'is_correct' => false // Temporary
            ];
        }
        // Assign correct option if found
        if ($correctIndex !== null) {
            $options[$correctIndex]['is_correct'] = true;
        } else {
            // Default to first option if no correct answer is marked
            $options[0]['is_correct'] = true;
        }
        if (allOptionsHaveTextAndIsCorrect($options)) {
            return $options;
        }
    }

    return null;
}

// --- Validate Options ---
function allOptionsHaveTextAndIsCorrect(array $options): bool {
    if (count($options) !== 4) {
        return false;
    }
    $correctCount = 0;
    foreach ($options as $option) {
        if (!isset($option['text']) || !is_string($option['text']) || empty(trim($option['text']))) {
            return false;
        }
        if (!isset($option['is_correct']) || !is_bool($option['is_correct'])) {
            return false;
        }
        if ($option['is_correct']) {
            $correctCount++;
        }
    }
    return $correctCount === 1; // Exactly one correct option
}

// --- Call API and Process Response ---
header('Content-Type: application/json');

$apiResult = callGeminiApi($apiUrl, $data);

if ($apiResult['success']) {
    $generatedText = trim($apiResult['data']['candidates'][0]['content']['parts'][0]['text']);
    
    // Handle JSON output for options
    if ($targetField === 'options') {
        try {
            $options = parseOptionsFallback($generatedText);
            if ($options === null) {
                error_log("Invalid options format from API: " . substr($generatedText, 0, 500));
                throw new Exception('AI returned invalid or malformed options. Please try a different input.');
            }
            echo json_encode(['generated_options' => $options]);
        } catch (Exception $e) {
            http_response_code(500);
            error_log("Options parsing error: " . $e->getMessage() . " - Raw response: " . substr($generatedText, 0, 500));
            echo json_encode(['error' => true, 'message' => 'Failed to generate valid options. Try rephrasing the input or generating again.']);
        }
    } else {
        // Clean up potential prefixes for text fields
        $generatedText = preg_replace('/^(Question|Explanation|Hint|Tags):\s*/i', '', $generatedText);
        echo json_encode(['generated_text' => $generatedText]);
    }
} else {
    $errorCode = $apiResult['httpCode'] >= 400 ? $apiResult['httpCode'] : 500;
    http_response_code($errorCode);
    echo json_encode([
        'error' => true,
        'message' => $apiResult['error'] ?? 'Unexpected error with AI service.',
    ]);
}

exit;