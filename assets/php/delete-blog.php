<?php
// delete-blog.php - Backend to delete news items from index

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

if (!isset($data['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing news ID']);
    exit;
}

$news_id = intval($data['id']);

// Update index file - path to the main latest news file
$index_file = dirname(__FILE__) . '/../data/latest-news.json';

// Check if file exists
if (!file_exists($index_file)) {
    http_response_code(404);
    echo json_encode(['error' => 'News file not found']);
    exit;
}

// Read existing index
$existing_data = file_get_contents($index_file);
$news_index = json_decode($existing_data, true);

if (!is_array($news_index)) {
    http_response_code(500);
    echo json_encode(['error' => 'Invalid news data format']);
    exit;
}

// Remove news article from index by filtering
$new_news_index = array_filter($news_index, function($news_item) use ($news_id) {
    return $news_item['id'] != $news_id;
});

// Re-index the array to reset array keys
$new_news_index = array_values($new_news_index);

// Save updated index
$index_output = json_encode($new_news_index, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
$write_result = file_put_contents($index_file, $index_output);

if ($write_result === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update news index. Check file permissions.']);
    exit;
}

http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'News item deleted successfully'
]);
?>
