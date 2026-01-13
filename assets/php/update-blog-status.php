<?php
// update-blog-status.php - Backend to update news article published status

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

// Path to the blogs.json file
$blogs_file = dirname(__FILE__) . '/../data/blogs.json';

// Check if file exists
if (!file_exists($blogs_file)) {
    http_response_code(404);
    echo json_encode(['error' => 'Blogs file not found']);
    exit;
}

// Read existing blogs
$existing_data = file_get_contents($blogs_file);
$blogs = json_decode($existing_data, true);

if (!is_array($blogs)) {
    http_response_code(500);
    echo json_encode(['error' => 'Invalid blogs data format']);
    exit;
}

// Find and update the blog
$found = false;
foreach ($blogs as &$blog) {
    if ($blog['id'] === $blog_id) {
        $blog['published'] = $published;
        $found = true;
        break;
    }
}

if (!$found) {
    http_response_code(404);
    echo json_encode(['error' => 'Blog not found']);
    exit;
}

// Save updated blogs
$blogs_output = json_encode($blogs, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
$write_result = file_put_contents($blogs_file, $blogs_output);

if ($write_result === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update blogs file. Check file permissions.']);
    exit;
}

http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Blog status updated successfully'
]);
?>
