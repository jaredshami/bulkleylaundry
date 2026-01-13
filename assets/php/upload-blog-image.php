<?php
// upload-blog-image.php - Handle news article image uploads

header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Check if file was uploaded
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['image'];
$upload_dir = __DIR__ . '/../images/blog/';

// Create directory if it doesn't exist
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create upload directory']);
        exit;
    }
}

// Validate file type
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($file['type'], $allowed_types)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid file type. Only JPG, PNG, GIF, and WebP allowed']);
    exit;
}

// Validate file size (max 5MB)
$max_size = 5 * 1024 * 1024; // 5MB
if ($file['size'] > $max_size) {
    http_response_code(400);
    echo json_encode(['error' => 'File too large. Maximum size is 5MB']);
    exit;
}

// Generate unique filename
$original_name = basename($file['name']);
$file_ext = pathinfo($original_name, PATHINFO_EXTENSION);
$file_name = 'blog-' . time() . '-' . uniqid() . '.' . $file_ext;
$file_path = $upload_dir . $file_name;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $file_path)) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save uploaded file']);
    exit;
}

// Return success with file path
http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Image uploaded successfully',
    'file_name' => $file_name,
    'file_path' => 'assets/images/blog/' . $file_name,
    'url' => '/bulkleylaundry/assets/images/blog/' . $file_name
]);
?>
