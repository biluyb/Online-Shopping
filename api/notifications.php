<?php
/**
 * ShopVerse - Notifications API
 * Handles AJAX requests for fetching and managing user notifications.
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure user is logged in
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

$user_id = $_SESSION['user']['id'];
$action = $_REQUEST['action'] ?? 'fetch';

try {
    if ($action === 'fetch') {
        // Get unread count
        $stmt_count = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
        $stmt_count->execute([$user_id]);
        $unread_count = $stmt_count->fetchColumn();
        
        // Get latest 5 notifications
        $stmt_latest = $pdo->prepare("SELECT id, type, title, message, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
        $stmt_latest->execute([$user_id]);
        $notifications = $stmt_latest->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true, 
            'unread_count' => $unread_count, 
            'notifications' => $notifications
        ]);
        
    } elseif ($action === 'mark_read') {
        $notification_id = (int)($_POST['notification_id'] ?? 0);
        
        if ($notification_id > 0) {
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?");
            $stmt->execute([$notification_id, $user_id]);
        } else {
            // Mark all as read
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
            $stmt->execute([$user_id]);
        }
        
        echo json_encode(['success' => true]);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}
