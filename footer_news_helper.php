<?php
// footer_news_helper.php

// ⚠️ Replace with your actual API keys securely (use environment variables in production)
define('GOOGLE_SEARCH_API_KEY', 'AIzaSyAu8t2mJwAN5pusGQNHyjOh98K28XUG5q8');
define('GOOGLE_SEARCH_CX_ID', 'b7e9b83d7f7c64edd');
define('GEMINI_API_KEY', 'AIzaSyDiD7MRHVtINKL64sRodLTP9LDQfJ4FWmY');

define('NEWS_CACHE_DIR', __DIR__ . '/cache');
define('NEWS_CACHE_FILE', NEWS_CACHE_DIR . '/dynamic_news_update.json');
define('NEWS_CACHE_EXPIRY', 3600); // 1 hour

if (!is_dir(NEWS_CACHE_DIR)) {
    @mkdir(NEWS_CACHE_DIR, 0755, true);
}

/**
 * Fetches news snippets using Google Custom Search API.
 */
function fetch_google_search_news($query, $numResults = 5) {
    if (!GOOGLE_SEARCH_API_KEY || !GOOGLE_SEARCH_CX_ID) {
        error_log("Google Search API key or CX ID not configured.");
        return null;
    }

    $apiUrl = "https://www.googleapis.com/customsearch/v1?key=" . GOOGLE_SEARCH_API_KEY .
              "&cx=" . GOOGLE_SEARCH_CX_ID .
              "&q=" . urlencode($query) .
              "&num=" . $numResults .
              "&sort=date";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        error_log("Google Search API Error: HTTP $httpCode - Response: $response");
        return null;
    }

    $data = json_decode($response, true);
    $snippets = [];

    if (isset($data['items'])) {
        foreach ($data['items'] as $item) {
            $title = $item['title'] ?? '';
            $snippet = $item['snippet'] ?? '';
            $snippets[] = "$title: $snippet";
        }
        return implode("\n\n", $snippets);
    }

    return null;
}

/**
 * Summarizes text using Gemini API.
 */
function get_gemini_news_summary($text) {
    if (!GEMINI_API_KEY) {
        error_log("Gemini API key not configured.");
        return null;
    }

    $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=" . GEMINI_API_KEY;

    $prompt = "You are an India and World news summarizer. Summarize the following news snippets in 1-2 neutral sentences:\n\n$text";

    $payload = json_encode([
        "contents" => [["parts" => [["text" => $prompt]]]],
        "generationConfig" => [
            "temperature" => 0.9,
            "maxOutputTokens" => 150
        ]
    ]);

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        error_log("Gemini API Error: HTTP $httpCode - Response: $response");
        return null;
    }

    $data = json_decode($response, true);
    return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
}

/**
 * Main function to get the dynamic news update.
 */
function get_dynamic_news_update() {
    $default_news = [
        'content' => 'Stay tuned for the latest updates. News is currently initializing.',
        'last_updated' => date('d-m-Y H:i:s')
    ];

    // Serve cached news if valid
    if (file_exists(NEWS_CACHE_FILE) && (time() - filemtime(NEWS_CACHE_FILE)) < NEWS_CACHE_EXPIRY) {
        $cached = json_decode(file_get_contents(NEWS_CACHE_FILE), true);
        if ($cached && isset($cached['content'], $cached['last_updated'])) {
            return $cached;
        }
    }

    $query = "Latest News in India";
    $snippets = fetch_google_search_news($query);

    if ($snippets) {
        $summary = get_gemini_news_summary($snippets);

        if ($summary) {
            $newsData = [
                'content' => trim($summary),
                'last_updated' => date('d-m-Y H:i:s')
            ];
            if (is_writable(NEWS_CACHE_DIR)) {
                if (!file_put_contents(NEWS_CACHE_FILE, json_encode($newsData))) {
                    error_log("Failed to write news cache.");
                }
            }
            return $newsData;
        } else {
            error_log("Gemini failed to summarize. Using fallback.");
        }
    } else {
        error_log("Failed to fetch snippets from Google Search.");
    }

    // Fallback: load old cache or return default
    if (file_exists(NEWS_CACHE_FILE)) {
        $oldData = json_decode(file_get_contents(NEWS_CACHE_FILE), true);
        if ($oldData) return $oldData;
    }

    $default_news['content'] = 'Live news currently unavailable. Please check back later.';
    return $default_news;
}

// Get the latest news update for footer
$newsUpdate = get_dynamic_news_update();
?>
