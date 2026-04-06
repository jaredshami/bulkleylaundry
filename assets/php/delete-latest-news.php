<?php
// delete-latest-news.php - Backend to delete a latest news article

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

if (!isset($data['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'News ID is required']);
    exit;
}

$news_id = intval($data['id']);
$news_file = __DIR__ . '/blogs/' . $news_id . '.json';

// Delete the news article file
if (file_exists($news_file)) {
    if (!unlink($news_file)) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete news file']);
        exit;
    }
}

// Remove from index file
$index_file = __DIR__ . '/../data/latest-news.json';
$success = false;

if (file_exists($index_file)) {
    $news_index = json_decode(file_get_contents($index_file), true);
    
    // Filter out the deleted article
    $filtered_index = array_filter($news_index, function($item) use ($news_id) {
        return $item['id'] != $news_id;
    });
    
    // Re-index array
    $filtered_index = array_values($filtered_index);
    
    if (count($filtered_index) < count($news_index)) {
        $success = true;
    }
    
    // Save updated index
    $index_output = json_encode($filtered_index, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if (file_put_contents($index_file, $index_output) === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update news index']);
        exit;
    }
}

if ($success) {
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'News article deleted successfully'
    ]);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'News article not found']);
}
?>
