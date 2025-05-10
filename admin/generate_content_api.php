<?php
// admin/generate_content_api.php

// Strict types and error reporting for development
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Disable displaying errors in production, rely on logs
ini_set('log_errors', '1');
// ini_set('error_log', '/path/to/your/php-error.log'); // Set a specific error log file if needed

require_once '../includes/database.php'; // Use __DIR__ for reliable path
require_once '../includes/auth.php';     // Use __DIR__ for reliable path

// --- Constants ---
define('GEMINI_API_ENDPOINT_BASE', 'https://generativelanguage.googleapis.com/v1beta/models/');
define('GEMINI_MODEL', 'gemini-2.0-flash'); // Or 'gemini-pro', etc.
define('DEFAULT_GENERATION_TARGET', 'generic');
define('CURL_TIMEOUT_SECONDS', 45); // Increased timeout slightly for potentially longer generations
define('CURL_CONNECTTIMEOUT_SECONDS', 10);

// --- Security: Authentication & Authorization ---
requireLogin();

// --- Security: Role Check (Recommended) ---
// Ensure the user has the appropriate role to access this feature.
// Adjust 'admin' or 'content_creator' based on your role system.
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'user'])) {
    http_response_code(403); // Forbidden
    header('Content-Type: application/json');
    echo json_encode(['error' => true, 'message' => 'Permission denied.']);
    exit;
}

// --- Get API Key Securely ---
$apiKey = 'AIzaSyDiD7MRHVtINKL64sRodLTP9LDQfJ4FWmY'; // Read from environment variable

if (empty($apiKey)) {
    http_response_code(500); // Internal Server Error
    error_log("FATAL ERROR: Gemini API Key (GEMINI_API_KEY) is not configured in environment variables.");
    header('Content-Type: application/json');
    echo json_encode(['error' => true, 'message' => 'AI service configuration error. Please contact administrator.']);
    exit;
}

// --- Request Validation ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    header('Content-Type: application/json');
    echo json_encode(['error' => true, 'message' => 'Invalid request method. Only POST is accepted.']);
    exit;
}

// Get and decode JSON payload
$jsonPayload = file_get_contents('php://input');
$requestData = json_decode($jsonPayload, true);

// Validate JSON decoding and presence of 'prompt'
if (json_last_error() !== JSON_ERROR_NONE || !isset($requestData['prompt'])) {
    http_response_code(400); // Bad Request
    header('Content-Type: application/json');
    echo json_encode(['error' => true, 'message' => 'Invalid JSON payload.']);
    exit;
}

// Validate and sanitize 'prompt'
$userPrompt = trim((string)$requestData['prompt']);
if (empty($userPrompt)) {
    http_response_code(400); // Bad Request
    header('Content-Type: application/json');
    echo json_encode(['error' => true, 'message' => 'Prompt cannot be empty.']);
    exit;
}

// Get and validate 'target' field
$targetField = isset($requestData['target']) ? trim((string)$requestData['target']) : DEFAULT_GENERATION_TARGET;
$allowedTargets = ['title', 'description', 'instructions', 'generic']; // Define allowed targets
if (!in_array($targetField, $allowedTargets)) {
    $targetField = DEFAULT_GENERATION_TARGET; // Default to generic if invalid target provided
    // Optionally, you could return an error here if the target MUST be one of the specific types
    // http_response_code(400);
    // echo json_encode(['error' => true, 'message' => 'Invalid target specified.']);
    // exit;
}


// --- Prepare Prompt for Gemini ---
// Extracted System Instructions into a Configurable Array
$systemInstructions = [
    'generic' => "You are 'Special BOX AI' assistant specialized for only generating content for online quizzes. Be concise, engaging, and directly address the user's request.",
    'title' => "You are an specialized expert in crafting catchy and relevant quiz titles only nothing else.",
    'description' => "You are skilled and specialized expert at writing brief, informative, and engaging quiz descriptions only nothing else.",
    'instructions' => "You are adept at creating clear, simple, step-by-step instructions for taking quizzes.",
];

$systemInstruction = $systemInstructions['generic']; // Default
if (isset($systemInstructions[$targetField])) {
    $systemInstruction = $systemInstructions[$targetField];
}

$fullPrompt = "";
$maxTokens = 256; // Default max tokens

switch ($targetField) {
    case 'title':
        $fullPrompt = $systemInstruction . "\n\nGenerate ONE concise, catchy, and relevant quiz title (max 10 words) based on this topic: \"{$userPrompt}\"\n\nOutput only the title text, nothing else.";
        $maxTokens = 50; // Titles are short
        break;
    case 'description':
        $fullPrompt = $systemInstruction . "\n\nGenerate a brief, informative, and engaging quiz description (around 2-4 sentences, max 75 words) for a quiz about: \"{$userPrompt}\"\n\nOutput only the description text, nothing else.";
        $maxTokens = 150;
        break;
    case 'instructions':
        // Note: Gemini doesn't guarantee specific formatting like '__' line breaks.
        // Asking for clear steps is more reliable.
        $fullPrompt = $systemInstruction . "\n\nGenerate clear, simple, step-by-step instructions (max 100 words (Also use __ when going to next row All contents in one particular line)) for taking a quiz related to: \"{$userPrompt}\". Assume the user is on the quiz start page. Use numbered points if appropriate. Output only the instructions text, nothing else.";
        $maxTokens = 200;
        break;
    default: // generic
        $fullPrompt = $systemInstruction . "\n\nGenerate relevant content based on the following user request: \"{$userPrompt}\"\n\nKeep the response concise and focused.";
        $maxTokens = 512; // Allow more flexibility for generic requests
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
    // --- Generation Configuration (Fine-tuning) ---
    'generationConfig' => [
        'temperature' => 0.7,       // Controls randomness (0=deterministic, 1=max creative)
        'topK' => 40,               // Considers the top K most likely tokens
        'topP' => 0.95,              // Uses nucleus sampling (considers tokens cumulative probability >= 0.95)
        'maxOutputTokens' => $maxTokens, // Max length of the generated response
        // 'stopSequences' => ["\n\n\n"] // Optional: sequences that stop generation
    ],
    // --- Safety Settings (Crucial for responsible AI) ---
    // Block thresholds: BLOCK_NONE, BLOCK_ONLY_HIGH, BLOCK_LOW_AND_ABOVE, BLOCK_MEDIUM_AND_ABOVE
    'safetySettings' => [
        [
            'category' => 'HARM_CATEGORY_HARASSMENT',
            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE',
        ],
        [
            'category' => 'HARM_CATEGORY_HATE_SPEECH',
            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE',
        ],
        [
            'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE',
        ],
        [
            'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE',
        ],
    ],
];


// --- Helper Function for API Call ---
/**
 * Calls the Gemini API using cURL.
 *
 * @param string $url The full API URL with API key.
 * @param array $payload The request data payload.
 * @return array ['success' => bool, 'data' => array|null, 'error' => string|null, 'httpCode' => int]
 */
function callGeminiApi(string $url, array $payload): array
{
    $jsonData = json_encode($payload);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['success' => false, 'data' => null, 'error' => 'Failed to encode API request data: ' . json_last_error_msg(), 'httpCode' => 500];
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
    // Optional: Add CA Certificate verification if needed on your server
    // curl_setopt($ch, CURLOPT_CAINFO, '/path/to/cacert.pem');
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);


    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErrorNo = curl_errno($ch);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlErrorNo !== CURLE_OK) {
        error_log("cURL Error calling Gemini API: (#{$curlErrorNo}) {$curlError} - URL: {$url}");
        return ['success' => false, 'data' => null, 'error' => "Failed to connect to AI service. cURL Error: {$curlError}", 'httpCode' => 500];
    }

    $responseData = json_decode((string)$response, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
         // Log the raw response if JSON decoding fails
        error_log("Gemini API Non-JSON Response: HTTP {$httpCode} - Response: " . substr((string)$response, 0, 500));
        return ['success' => false, 'data' => null, 'error' => 'Received an invalid response format from AI service.', 'httpCode' => $httpCode];
    }

    // Check for API-level errors reported within the JSON structure
    if ($httpCode >= 400 || isset($responseData['error'])) {
        $apiErrorMessage = $responseData['error']['message'] ?? 'Unknown API error';
        $details = $responseData['error'] ?? $response; // Include full error structure if available
        error_log("Gemini API Error: HTTP {$httpCode} - Message: {$apiErrorMessage} - Details: " . json_encode($details));
        return ['success' => false, 'data' => $responseData, 'error' => "AI service returned an error: {$apiErrorMessage}", 'httpCode' => $httpCode];
    }

    // Check if content was generated or blocked
    if (!isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
        $blockReason = $responseData['promptFeedback']['blockReason'] ?? 'Unknown reason';
        $safetyRatings = $responseData['promptFeedback']['safetyRatings'] ?? null;
        error_log("Gemini API: No content generated. Block Reason: {$blockReason} - Safety Ratings: " . json_encode($safetyRatings));

        $userMessage = 'AI could not generate content.';
        if ($blockReason !== 'Unknown reason') {
            $userMessage .= ' Reason: ' . str_replace('_', ' ', $blockReason) . '. Try rephrasing your prompt.';
            // Avoid showing 'SAFETY' directly, maybe map to a generic message
             if ($blockReason === 'SAFETY') {
                 $userMessage = 'AI could not generate content due to safety filters. Please modify your prompt and try again.';
             }
        }
        return ['success' => false, 'data' => $responseData, 'error' => $userMessage, 'httpCode' => 400]; // Bad request likely due to prompt
    }

    // Success
    return ['success' => true, 'data' => $responseData, 'error' => null, 'httpCode' => $httpCode];
}

// --- Call API and Process Response ---
header('Content-Type: application/json');

$apiResult = callGeminiApi($apiUrl, $data);

if ($apiResult['success']) {
    // Success! Extract the text.
    $generatedText = $apiResult['data']['candidates'][0]['content']['parts'][0]['text'];

    // Basic cleanup - trim whitespace. Add more if needed (e.g., removing markdown prefixes if they appear).
    $generatedText = trim($generatedText);

    // Specific cleanup for title/description/instructions if model adds extra text despite instructions
    if (in_array($targetField, ['title', 'description', 'instructions'])) {
       // Example: remove potential "Title:", "Description:", "Instructions:" prefixes
       $generatedText = preg_replace('/^(Title|Description|Instructions):\s*/i', '', $generatedText);
    }
    // You might add more specific regex cleanups here based on observed model behavior

    echo json_encode(['generated_text' => $generatedText]);

} else {
    // Handle Failure
    $errorCode = $apiResult['httpCode'] >= 400 ? $apiResult['httpCode'] : 500; // Ensure a client or server error code
    http_response_code($errorCode);
    echo json_encode([
        'error' => true,
        'message' => $apiResult['error'] ?? 'An unexpected error occurred with the AI service.',
        'details' => ($errorCode >= 500) ? 'Internal server error or connection issue.' : 'Error processing request or prompt rejected.' // Provide less detail to client for 5xx
        // 'raw_api_response' => ($errorCode < 500) ? $apiResult['data'] : null // Optionally include raw response for debugging 4xx errors
    ]);
}

exit;