<?php
// update-latest-news-status.php - Backend to update published status of a latest news article

header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get the JSON data from request body
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!isset($data['id']) || !isset($data['published'])) {
    http_response_code(400);
    echo json_encode(['error' => 'News ID and published status are required']);
    exit;
}

$news_id = intval($data['id']);
$published = (bool)$data['published'];

// Update the index file
$index_file = __DIR__ . '/../data/latest-news.json';

if (!file_exists($index_file)) {
    http_response_code(404);
    echo json_encode(['error' => 'News index not found']);
    exit;
}

$news_index = json_decode(file_get_contents($index_file), true);

// Find and update the article status
$found = false;
foreach ($news_index as &$item) {
    if ($item['id'] == $news_id) {
        $item['published'] = $published;
        $found = true;
        break;
    }
}

if (!$found) {
    http_response_code(404);
    echo json_encode(['error' => 'News article not found']);
    exit;
}

// Save updated index
$index_output = json_encode($news_index, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

if (file_put_contents($index_file, $index_output) === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update news status']);
    exit;
}

http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'News article status updated successfully',
    'published' => $published
]);
?>
