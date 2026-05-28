<?php
/**
 * Online shopping registration system Admin Orders Panel
 * View and process customer orders.
 */

$page_title = 'Orders';
require_once __DIR__ . '/admin_header.php';

$action = $_GET['action'] ?? 'list';
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update_status' && $order_id > 0) {
    $new_status = sanitize_input($_POST['status'] ?? '');
    $valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    
    if (in_array($new_status, $valid_statuses)) {
        try {
            $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$new_status, $order_id]);
            $_SESSION['flash_message'] = "Order status updated to " . ucfirst($new_status) . ".";
            $_SESSION['flash_type'] = "success";
        } catch (PDOException $e) {
            $_SESSION['flash_message'] = "Failed to update order status.";
            $_SESSION['flash_type'] = "error";
        }
    }
    redirect("orders.php?action=view&id={$order_id}");
    exit;
}

if ($action === 'view' && $order_id > 0):
    // Fetch Order Details
    try {
        $stmt = $pdo->prepare("
            SELECT o.*, u.full_name, u.email, u.phone 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = ?
        ");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            $_SESSION['flash_message'] = "Order not found.";
            $_SESSION['flash_type'] = "error";
            redirect('orders.php');
            exit;
        }

        // Fetch Order Items
        $stmt_items = $pdo->prepare("
            SELECT oi.*, p.name as product_name, p.image as product_image 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = ?
        ");
        $stmt_items->execute([$order_id]);
        $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $order = null;
        $items = [];
    }
?>
    <div class="page-header">
        <div>
            <h1>Order #ORD-<?php echo str_pad($order['id'], 4, '0', STR_PAD_LEFT); ?></h1>
            <div class="breadcrumb">
                <a href="index.php">Admin</a> / <a href="orders.php">Orders</a> / View Order
            </div>
        </div>
        <div class="header-actions">
            <a href="orders.php" class="btn" style="background: #2d3748; color: #e4e6eb;">
                <i class="fas fa-arrow-left"></i> Back to Orders
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="admin-card">
                <div class="card-header">
                    <h3>Order Items</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th width="60">Image</th>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th style="text-align: right;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <?php if ($item['product_image']): ?>
                                            <img src="../<?php echo htmlspecialchars($item['product_image']); ?>" alt="Product" class="product-thumb">
                                        <?php else: ?>
                                            <div class="product-thumb" style="display: flex; align-items: center; justify-content: center; background: #2d3748; color: #6b7280;">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($item['product_name']); ?></strong></td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td style="text-align: right;"><strong>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" style="text-align: right; border-top: 2px solid #2d3748; padding-top: 16px;"><strong>Subtotal:</strong></td>
                                <td style="text-align: right; border-top: 2px solid #2d3748; padding-top: 16px;">$<?php echo number_format($order['total_amount'], 2); ?></td>
                            </tr>
                            <tr>
                                <td colspan="4" style="text-align: right; padding-top: 8px;"><strong>Shipping:</strong></td>
                                <td style="text-align: right; padding-top: 8px;">$0.00</td>
                            </tr>
                            <tr>
                                <td colspan="4" style="text-align: right; padding-top: 16px; font-size: 1.1rem; color: #e4e6eb;"><strong>Total:</strong></td>
                                <td style="text-align: right; padding-top: 16px; font-size: 1.1rem; color: #818cf8;"><strong>$<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="admin-card">
                <div class="card-header">
                    <h3>Order Status</h3>
                </div>
                <form method="POST" action="?action=update_status&id=<?php echo $order['id']; ?>" class="admin-form">
                    <div class="form-group mb-3">
                        <select name="status" class="form-control" style="font-weight: bold; padding: 12px;">
                            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="shipped" <?php echo $order['status'] === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                            <option value="delivered" <?php echo $order['status'] === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Update Status</button>
                </form>
            </div>
            
            <div class="admin-card">
                <div class="card-header">
                    <h3>Customer Details</h3>
                </div>
                <div style="font-size: 0.9rem; line-height: 1.6; color: #d1d5db;">
                    <p style="margin-bottom: 8px;"><strong>Name:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
                    <p style="margin-bottom: 8px;"><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($order['email']); ?>" style="color: #818cf8;"><?php echo htmlspecialchars($order['email']); ?></a></p>
                    <p style="margin-bottom: 8px;"><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone'] ?? 'N/A'); ?></p>
                </div>
                
                <h4 style="font-size: 0.95rem; margin-top: 20px; margin-bottom: 12px; color: #e4e6eb; border-bottom: 1px solid #2d3748; padding-bottom: 8px;">Shipping Address</h4>
                <div style="font-size: 0.9rem; line-height: 1.6; color: #d1d5db;">
                    <?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <?php
    // Fetch orders list
    $filter_status = sanitize_input($_GET['status'] ?? '');
    
    $where_clauses = [];
    $params = [];
    
    if ($filter_status) {
        $where_clauses[] = "o.status = ?";
        $params[] = $filter_status;
    }
    
    $where_sql = $where_clauses ? 'WHERE ' . implode(' AND ', $where_clauses) : '';
    
    try {
        $stmt = $pdo->prepare("
            SELECT o.id, o.total_amount, o.status, o.created_at, u.full_name 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            $where_sql 
            ORDER BY o.created_at DESC
        ");
        $stmt->execute($params);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $orders = [];
    }
    ?>
    <div class="page-header">
        <div>
            <h1>Orders</h1>
            <div class="breadcrumb">
                <a href="index.php">Admin</a> / Orders
            </div>
        </div>
    </div>

    <div class="admin-card">
        <div class="filter-tabs">
            <a href="orders.php" class="<?php echo empty($filter_status) ? 'active' : ''; ?>">All Orders</a>
            <a href="orders.php?status=pending" class="<?php echo $filter_status === 'pending' ? 'active' : ''; ?>">Pending</a>
            <a href="orders.php?status=processing" class="<?php echo $filter_status === 'processing' ? 'active' : ''; ?>">Processing</a>
            <a href="orders.php?status=shipped" class="<?php echo $filter_status === 'shipped' ? 'active' : ''; ?>">Shipped</a>
            <a href="orders.php?status=delivered" class="<?php echo $filter_status === 'delivered' ? 'active' : ''; ?>">Delivered</a>
            <a href="orders.php?status=cancelled" class="<?php echo $filter_status === 'cancelled' ? 'active' : ''; ?>">Cancelled</a>
        </div>

        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th style="text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($orders) > 0): ?>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td><strong>#ORD-<?php echo str_pad($o['id'], 4, '0', STR_PAD_LEFT); ?></strong></td>
                                <td><?php echo htmlspecialchars($o['full_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($o['created_at'])); ?></td>
                                <td><strong>$<?php echo number_format($o['total_amount'], 2); ?></strong></td>
                                <td>
                                    <span class="status-badge <?php echo htmlspecialchars($o['status']); ?>">
                                        <?php echo ucfirst(htmlspecialchars($o['status'])); ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <a href="?action=view&id=<?php echo $o['id']; ?>" class="btn btn-sm btn-primary" style="padding: 6px 12px;">
                                        Manage
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="fas fa-file-invoice"></i>
                                    <p>No orders found.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
