<?php
header('Content-Type: text/plain; charset=utf-8');

error_log("PHP proxy called!");

// Get the URL from query parameter
if (!isset($_GET['url'])) {
    error_log("No URL parameter");
    http_response_code(400);
    echo "Error: Missing 'url' parameter";
    exit;
}

$url = $_GET['url'];
error_log("URL: " . $url);

// Construct the export URL
if (strpos($url, '/e/') !== false) {
    $match = [];
    preg_match('/\/e\/([a-zA-Z0-9-_]+)/', $url, $match);
    if (!empty($match)) {
        $docId = $match[1];
        $exportUrl = "https://docs.google.com/document/d/e/{$docId}/export?format=txt";
        error_log("Export URL: " . $exportUrl);
    } else {
        error_log("Could not extract doc ID");
        http_response_code(400);
        echo "Error: Could not extract document ID from URL";
        exit;
    }
} else {
    error_log("Unsupported URL format");
    http_response_code(400);
    echo "Error: Unsupported URL format";
    exit;
}

// Fetch the content
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    ]
]);

$content = @file_get_contents($exportUrl, false, $context);

if ($content === false) {
    http_response_code(404);
    echo "Error: Could not fetch Google Doc. Make sure the sharing is set to 'Anyone with the link can view'.";
    exit;
}

echo $content;
?>
