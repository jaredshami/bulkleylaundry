<?php
// list-blog-images.php - Return list of previously uploaded news images

header('Content-Type: application/json');

$images_dir = __DIR__ . '/assets/images/blog';
$images = [];

if (is_dir($images_dir)) {
    $files = scandir($images_dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..' || is_dir($images_dir . '/' . $file)) {
            continue;
        }
        
        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $images[] = [
                'name' => $file,
                'path' => 'assets/images/blog/' . $file
            ];
        }
    }
}

// Sort by name, newest first (assuming naming convention includes timestamp)
usort($images, function($a, $b) {
    return strcmp($b['name'], $a['name']);
});

echo json_encode($images);
?>
