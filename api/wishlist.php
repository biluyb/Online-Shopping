<?php
/**
 * ShopVerse - Wishlist API
 * Handles AJAX requests for toggling items in the wishlist.
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

$action = $_POST['action'] ?? '';
$product_id = (int)($_POST['product_id'] ?? 0);
$user_id = $_SESSION['user']['id'] ?? null;
$session_id = session_id();

if ($product_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product.']);
    exit;
}

// Ensure product exists
try {
    $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    if (!$stmt->fetchColumn()) {
        echo json_encode(['success' => false, 'message' => 'Product not found.']);
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error.']);
    exit;
}

try {
    if ($action === 'add') {
        // Check if already in wishlist
        if ($user_id) {
            $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
        } else {
            $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE session_id = ? AND product_id = ? AND user_id IS NULL");
            $stmt->execute([$session_id, $product_id]);
        }
        
        if (!$stmt->fetchColumn()) {
            if ($user_id) {
                $stmt = $pdo->prepare("INSERT INTO wishlist (user_id, session_id, product_id) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $session_id, $product_id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO wishlist (session_id, product_id) VALUES (?, ?)");
                $stmt->execute([$session_id, $product_id]);
            }
        }
        
        echo json_encode(['success' => true]);
        
    } elseif ($action === 'remove') {
        if ($user_id) {
            $stmt = $pdo->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$user_id, $product_id]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM wishlist WHERE session_id = ? AND product_id = ? AND user_id IS NULL");
            $stmt->execute([$session_id, $product_id]);
        }
        
        echo json_encode(['success' => true]);
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to update wishlist.']);
}
