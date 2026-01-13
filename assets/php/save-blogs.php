<?php
// save-blogs.php - Backend to save individual news article JSON files

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
$news = json_decode($json_data, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

// Validate required fields for news article
$required_fields = ['id', 'title', 'slug', 'date', 'author', 'category', 'excerpt', 'content'];
foreach ($required_fields as $field) {
    if (!isset($news[$field]) || trim($news[$field]) === '') {
        http_response_code(400);
        echo json_encode(['error' => "Missing required field: $field"]);
        exit;
    }
}

// Ensure optional fields are preserved (but allow null)
if (!isset($news['image_path'])) {
    $news['image_path'] = null;
}
if (!isset($news['video_url'])) {
    $news['video_url'] = null;
}

$news_id = intval($news['id']);
$news_dir = __DIR__ . '/blogs';
$news_file = $news_dir . '/' . $news_id . '.json';

// Create news directory if it doesn't exist
if (!is_dir($news_dir)) {
    if (!mkdir($news_dir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create news directory']);
        exit;
    }
}

// Save individual news file
$json_output = json_encode($news, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

if (file_put_contents($news_file, $json_output) === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save news file']);
    exit;
}

// Update or create index file - save to /data directory where it's actually loaded from
$index_file = __DIR__ . '/../data/blogs.json';
$news_index = [];

// Read existing index
if (file_exists($index_file)) {
    $existing_data = json_decode(file_get_contents($index_file), true);
    if (is_array($existing_data)) {
        $news_index = $existing_data;
    }
}

// Check if news article already exists in index
$news_exists = false;
foreach ($news_index as &$existing_news) {
    if ($existing_news['id'] == $news_id) {
        // Update existing entry (preserve published status)
        $published = isset($existing_news['published']) ? $existing_news['published'] : true;
        $existing_news = [
            'id' => $news['id'],
            'title' => $news['title'],
            'slug' => $news['slug'],
            'date' => $news['date'],
            'author' => $news['author'],
            'category' => $news['category'],
            'excerpt' => $news['excerpt'],
            'content' => $news['content'],
            'image_path' => $news['image_path'],
            'video_url' => $news['video_url'],
            'published' => $published
        ];
        $news_exists = true;
        break;
    }
}

// Add new news article to index if not exists
if (!$news_exists) {
    $news_index[] = [
        'id' => $news['id'],
        'title' => $news['title'],
        'slug' => $news['slug'],
        'date' => $news['date'],
        'author' => $news['author'],
        'category' => $news['category'],
        'excerpt' => $news['excerpt'],
        'content' => $news['content'],
        'image_path' => $news['image_path'],
        'video_url' => $news['video_url'],
        'published' => false
    ];
}

// Save updated index
$index_output = json_encode($news_index, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if (file_put_contents($index_file, $index_output) === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update news index']);
    exit;
}

http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'News article saved successfully',
    'news_id' => $news_id
]);
?>
