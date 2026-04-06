<?php
// update-latest-news-status.php - Backend to update latest news article published status

header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get the JSON data from request body
$json_data = file_get_contents('php://input');

// Validate JSON
$data = json_decode($json_data, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

if (!isset($data['id']) || !isset($data['published'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing id or published status']);
    exit;
}

$blog_id = intval($data['id']);
$published = (bool)$data['published'];

// Path to the latest news data file
$news_file = dirname(__FILE__) . '/../data/latest-news.json';

// Check if file exists
if (!file_exists($news_file)) {
    http_response_code(404);
    echo json_encode(['error' => 'Latest news file not found']);
    exit;
}

// Read existing news items
$existing_data = file_get_contents($news_file);
$news_items = json_decode($existing_data, true);

if (!is_array($news_items)) {
    http_response_code(500);
    echo json_encode(['error' => 'Invalid latest news data format']);
    exit;
}

// Find and update the news item
$found = false;
foreach ($news_items as &$item) {
    if ($item['id'] === $blog_id) {
        $item['published'] = $published;
        $found = true;
        break;
    }
}

if (!$found) {
    http_response_code(404);
    echo json_encode(['error' => 'News item not found']);
    exit;
}

// Save updated news items
$news_output = json_encode($news_items, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
$write_result = file_put_contents($news_file, $news_output);

if ($write_result === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update latest news file. Check file permissions.']);
    exit;
}

http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Latest news status updated successfully'
]);
?>
