<?php
/**
 * ShopVerse - User Management API
 * Handles AJAX requests for user profile updates.
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Ensure user is logged in
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in.']);
    exit;
}

$action = $_POST['action'] ?? '';
$user_id = $_SESSION['user']['id'];

if ($action === 'update_profile') {
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $shipping_address = sanitize_input($_POST['shipping_address'] ?? '');
    
    if (empty($full_name)) {
        echo json_encode(['success' => false, 'message' => 'Full name is required.']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ?, shipping_address = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$full_name, $phone, $shipping_address, $user_id]);
        
        // Update session
        $_SESSION['user']['full_name'] = $full_name;
        
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
} elseif ($action === 'update_password') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit;
    }
    
    if ($new_password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'New passwords do not match.']);
        exit;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $hash = $stmt->fetchColumn();
        
        if (!password_verify($current_password, $hash)) {
            echo json_encode(['success' => false, 'message' => 'Incorrect current password.']);
            exit;
        }
        
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$new_hash, $user_id]);
        
        echo json_encode(['success' => true, 'message' => 'Password updated successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
}
