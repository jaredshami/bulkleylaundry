<?php
header('Content-Type: application/json');

$testimonials_file = dirname(__FILE__) . '/../data/testimonials.json';
$action = $_POST['action'] ?? '';

if ($action === 'get_all') {
    // Get all testimonials
    if (!file_exists($testimonials_file)) {
        echo json_encode([]);
        exit;
    }
    
    $json_data = file_get_contents($testimonials_file);
    $testimonials = json_decode($json_data, true);
    echo json_encode($testimonials ?? []);
    exit;
}

if ($action === 'approve') {
    // Approve a testimonial
    $testimonial_id = intval($_POST['id'] ?? 0);
    
    if (!$testimonial_id || !file_exists($testimonials_file)) {
        echo json_encode(['success' => false, 'message' => 'Invalid testimonial']);
        exit;
    }
    
    $json_data = file_get_contents($testimonials_file);
    $testimonials = json_decode($json_data, true);
    
    $found = false;
    foreach ($testimonials as &$t) {
        if ($t['id'] === $testimonial_id) {
            $t['approved'] = true;
            $found = true;
            break;
        }
    }
    
    if ($found) {
        $json_content = json_encode($testimonials, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if (file_put_contents($testimonials_file, $json_content)) {
            echo json_encode(['success' => true, 'message' => 'Testimonial approved']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Testimonial not found']);
    }
    exit;
}

if ($action === 'delete') {
    // Delete a testimonial
    $testimonial_id = intval($_POST['id'] ?? 0);
    
    if (!$testimonial_id || !file_exists($testimonials_file)) {
        echo json_encode(['success' => false, 'message' => 'Invalid testimonial']);
        exit;
    }
    
    $json_data = file_get_contents($testimonials_file);
    $testimonials = json_decode($json_data, true);
    
    $testimonials = array_filter($testimonials, function($t) use ($testimonial_id) {
        return $t['id'] !== $testimonial_id;
    });
    
    // Re-index array
    $testimonials = array_values($testimonials);
    
    $json_content = json_encode($testimonials, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if (file_put_contents($testimonials_file, $json_content)) {
        echo json_encode(['success' => true, 'message' => 'Testimonial deleted']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>
