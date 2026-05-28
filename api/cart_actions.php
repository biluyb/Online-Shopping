<?php
/**
 * ShopVerse - Cart Actions API
 * Handles AJAX requests for adding, updating, and removing cart items.
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
$user_id = $_SESSION['user']['id'] ?? null;
$session_id = session_id();

// Helper to get total cart count
function getCartCount($pdo, $user_id, $session_id) {
    if ($user_id) {
        $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
    } else {
        $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE session_id = ? AND user_id IS NULL");
        $stmt->execute([$session_id]);
    }
    return (int)$stmt->fetchColumn() ?: 0;
}

try {
    switch ($action) {
        case 'add':
            $product_id = (int)($_POST['product_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 1);
            
            if ($product_id <= 0 || $quantity <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid product or quantity.']);
                exit;
            }
            
            // Check stock
            $stmt = $pdo->prepare("SELECT stock, name FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$product) {
                echo json_encode(['success' => false, 'message' => 'Product not found.']);
                exit;
            }
            
            // Check existing cart item
            if ($user_id) {
                $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
                $stmt->execute([$user_id, $product_id]);
            } else {
                $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE session_id = ? AND product_id = ? AND user_id IS NULL");
                $stmt->execute([$session_id, $product_id]);
            }
            $existing_item = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $new_quantity = $existing_item ? ($existing_item['quantity'] + $quantity) : $quantity;
            
            if ($new_quantity > $product['stock']) {
                echo json_encode(['success' => false, 'message' => 'Not enough stock available.']);
                exit;
            }
            
            if ($existing_item) {
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$new_quantity, $existing_item['id']]);
            } else {
                if ($user_id) {
                    $stmt = $pdo->prepare("INSERT INTO cart (user_id, session_id, product_id, quantity) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$user_id, $session_id, $product_id, $quantity]);
                } else {
                    $stmt = $pdo->prepare("INSERT INTO cart (session_id, product_id, quantity) VALUES (?, ?, ?)");
                    $stmt->execute([$session_id, $product_id, $quantity]);
                }
            }
            
            $cart_count = getCartCount($pdo, $user_id, $session_id);
            echo json_encode(['success' => true, 'cart_count' => $cart_count]);
            break;

        case 'update':
            $cart_id = (int)($_POST['cart_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 1);
            
            if ($cart_id <= 0 || $quantity <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid cart item or quantity.']);
                exit;
            }
            
            // Verify ownership and get product stock
            if ($user_id) {
                $stmt = $pdo->prepare("SELECT c.id, c.product_id, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.id = ? AND c.user_id = ?");
                $stmt->execute([$cart_id, $user_id]);
            } else {
                $stmt = $pdo->prepare("SELECT c.id, c.product_id, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.id = ? AND c.session_id = ? AND c.user_id IS NULL");
                $stmt->execute([$cart_id, $session_id]);
            }
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$item) {
                echo json_encode(['success' => false, 'message' => 'Cart item not found.']);
                exit;
            }
            
            if ($quantity > $item['stock']) {
                echo json_encode(['success' => false, 'message' => 'Not enough stock available.']);
                exit;
            }
            
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$quantity, $cart_id]);
            
            $cart_count = getCartCount($pdo, $user_id, $session_id);
            echo json_encode(['success' => true, 'cart_count' => $cart_count]);
            break;

        case 'remove':
            $cart_id = (int)($_POST['cart_id'] ?? 0);
            
            if ($cart_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid cart item.']);
                exit;
            }
            
            // Verify ownership and delete
            if ($user_id) {
                $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
                $stmt->execute([$cart_id, $user_id]);
            } else {
                $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND session_id = ? AND user_id IS NULL");
                $stmt->execute([$cart_id, $session_id]);
            }
            
            if ($stmt->rowCount() > 0) {
                $cart_count = getCartCount($pdo, $user_id, $session_id);
                echo json_encode(['success' => true, 'cart_count' => $cart_count]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to remove item.']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    }
} catch (PDOException $e) {
    // Log error in production, but for now just return generic error
    echo json_encode(['success' => false, 'message' => 'Database error.']);
}
