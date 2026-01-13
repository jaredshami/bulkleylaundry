<?php
header('Content-Type: application/json');

// Get the path to testimonials.json
$testimonials_file = dirname(__FILE__) . '/../data/testimonials.json';

// Validate input
$author = isset($_POST['author']) ? trim($_POST['author']) : '';
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
$text = isset($_POST['text']) ? trim($_POST['text']) : '';

// Validate required fields
if (empty($author) || empty($title) || empty($text) || $rating < 1 || $rating > 5) {
    echo json_encode([
        'success' => false,
        'message' => 'Please fill in all fields correctly.'
    ]);
    exit;
}

// Validate text length
if (strlen($text) < 10 || strlen($text) > 1000) {
    echo json_encode([
        'success' => false,
        'message' => 'Testimonial must be between 10 and 1000 characters.'
    ]);
    exit;
}

// Read existing testimonials
if (!file_exists($testimonials_file)) {
    $testimonials = [];
} else {
    $json_data = file_get_contents($testimonials_file);
    $testimonials = json_decode($json_data, true);
    if (!is_array($testimonials)) {
        $testimonials = [];
    }
}

// Create new testimonial
$new_id = 1;
if (!empty($testimonials)) {
    $new_id = max(array_column($testimonials, 'id')) + 1;
}

$new_testimonial = [
    'id' => $new_id,
    'author' => htmlspecialchars($author),
    'title' => htmlspecialchars($title),
    'rating' => $rating,
    'text' => htmlspecialchars($text),
    'approved' => false,
    'date' => date('Y-m-d')
];

// Add new testimonial
$testimonials[] = $new_testimonial;

// Save to file
$json_content = json_encode($testimonials, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if (file_put_contents($testimonials_file, $json_content)) {
    echo json_encode([
        'success' => true,
        'message' => 'Testimonial submitted successfully!'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save testimonial. Please try again.'
    ]);
}
?>
