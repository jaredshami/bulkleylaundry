<?php
header('Content-Type: application/json');

$contacts_file = dirname(__FILE__) . '/../data/contacts.json';

// Ensure contacts file exists
if (!file_exists($contacts_file)) {
    file_put_contents($contacts_file, '[]');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Submit new contact form
    if ($action === 'submit') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $service = trim($_POST['service'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $subscribe = isset($_POST['subscribe']) ? true : false;
        
        // Validate required fields (service is optional)
        if (empty($name) || empty($email) || empty($message)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Please provide name, email, and message']);
            exit;
        }
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            exit;
        }
        
        try {
            // Read existing contacts
            $contacts = [];
            if (file_exists($contacts_file) && is_readable($contacts_file)) {
                $json_data = file_get_contents($contacts_file);
                $contacts = json_decode($json_data, true) ?? [];
            }
            
            // Generate ID
            $newId = empty($contacts) ? 1 : max(array_column($contacts, 'id')) + 1;
            
            // Create new contact
            $newContact = [
                'id' => $newId,
                'name' => htmlspecialchars($name, ENT_QUOTES, 'UTF-8'),
                'email' => htmlspecialchars($email, ENT_QUOTES, 'UTF-8'),
                'phone' => htmlspecialchars($phone, ENT_QUOTES, 'UTF-8'),
                'service' => htmlspecialchars($service, ENT_QUOTES, 'UTF-8'),
                'message' => htmlspecialchars($message, ENT_QUOTES, 'UTF-8'),
                'subscribe' => $subscribe,
                'date' => date('Y-m-d H:i:s'),
                'read' => false,
                'archived' => false
            ];
            
            $contacts[] = $newContact;
            
            // Save contacts
            $json_content = json_encode($contacts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            
            if (!is_writable(dirname($contacts_file))) {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Server cannot write to contacts file']);
                exit;
            }
            
            // Send response immediately before file write
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Contact form submitted successfully']);
            
            // Flush output and close connection
            flush();
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }
            
            // Write file in background
            file_put_contents($contacts_file, $json_content);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
        exit;
    }
    
    // Get all contacts (admin only)
    if ($action === 'get_all') {
        if (!file_exists($contacts_file)) {
            echo json_encode([]);
            exit;
        }
        
        $json_data = file_get_contents($contacts_file);
        $contacts = json_decode($json_data, true) ?? [];
        echo json_encode($contacts);
        exit;
    }
    
    // Mark as read
    if ($action === 'mark_read') {
        $contact_id = intval($_POST['id'] ?? 0);
        
        if (!$contact_id || !file_exists($contacts_file)) {
            echo json_encode(['success' => false, 'message' => 'Invalid contact']);
            exit;
        }
        
        $json_data = file_get_contents($contacts_file);
        $contacts = json_decode($json_data, true);
        
        foreach ($contacts as &$c) {
            if ($c['id'] === $contact_id) {
                $c['read'] = true;
                break;
            }
        }
        
        $json_content = json_encode($contacts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if (file_put_contents($contacts_file, $json_content)) {
            echo json_encode(['success' => true, 'message' => 'Contact marked as read']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to update']);
        }
        exit;
    }
    
    // Archive contact
    if ($action === 'archive') {
        $contact_id = intval($_POST['id'] ?? 0);
        
        if (!$contact_id || !file_exists($contacts_file)) {
            echo json_encode(['success' => false, 'message' => 'Invalid contact']);
            exit;
        }
        
        $json_data = file_get_contents($contacts_file);
        $contacts = json_decode($json_data, true);
        
        foreach ($contacts as &$c) {
            if ($c['id'] === $contact_id) {
                $c['archived'] = true;
                break;
            }
        }
        
        $json_content = json_encode($contacts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if (file_put_contents($contacts_file, $json_content)) {
            echo json_encode(['success' => true, 'message' => 'Contact archived']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to archive']);
        }
        exit;
    }
    
    // Delete contact
    if ($action === 'delete') {
        $contact_id = intval($_POST['id'] ?? 0);
        
        if (!$contact_id || !file_exists($contacts_file)) {
            echo json_encode(['success' => false, 'message' => 'Invalid contact']);
            exit;
        }
        
        $json_data = file_get_contents($contacts_file);
        $contacts = json_decode($json_data, true);
        
        $contacts = array_filter($contacts, function($c) use ($contact_id) {
            return $c['id'] !== $contact_id;
        });
        
        // Re-index array
        $contacts = array_values($contacts);
        
        $json_content = json_encode($contacts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if (file_put_contents($contacts_file, $json_content)) {
            echo json_encode(['success' => true, 'message' => 'Contact deleted']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete']);
        }
        exit;
    }
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>
