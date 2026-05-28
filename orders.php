<?php
/**
 * Online shopping registration system - Customer Orders History Ledger
 */

require_once 'includes/auth_check.php';

$user_id = $_SESSION['user_id'];
$orders = [];

try {
    // Fetch all user orders
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll();
    
    // Unread notifications count
    $stmt2 = $pdo->prepare("SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt2->execute([$user_id]);
    $unread_notifs = (int)$stmt2->fetch()['total'];
} catch (PDOException $e) {
    // Fail gracefully
}

$page_title = "My Orders Ledger";
require_once 'includes/header.php';
?>

<!-- Custom styles -->
<link rel="stylesheet" href="css/dashboard.css">

<!-- ══════════════════════════════════════════════════════
     Breadcrumbs
     ══════════════════════════════════════════════════════ -->
<div class="py-4 mb-4" style="background: linear-gradient(135deg, rgba(124, 58, 237, 0.03) 0%, rgba(99, 102, 241, 0.03) 100%); border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <h1 class="fw-bold mb-2">My Orders</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Orders</li>
            </ol>
        </nav>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     Main Layout Split Grid
     ══════════════════════════════════════════════════════ -->
<div class="container py-4">
    <div class="dashboard-grid">
        <!-- Sidebar Navigation Column -->
        <aside class="d-flex flex-column gap-4">
            <div class="user-profile-card">
                <div class="avatar-wrapper mb-3">
                    <img src="uploads/avatars/<?php echo htmlspecialchars($_SESSION['user_avatar'] ?? 'default.png'); ?>" alt="Avatar" class="avatar-img">
                </div>
                <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($_SESSION['user_name']); ?></h5>
                <p class="text-secondary small mb-0"><?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
                <span class="badge bg-secondary-subtle text-secondary mt-2 small" style="border-radius:50px; font-weight:600;">Standard Profile</span>
            </div>

            <div class="menu-list-group">
                <a href="dashboard.php" class="menu-list-item">
                    <i class="fas fa-tachometer-alt"></i> Dashboard Overview
                </a>
                <a href="profile.php" class="menu-list-item">
                    <i class="fas fa-user-cog"></i> Profile Settings
                </a>
                <a href="orders.php" class="menu-list-item active">
                    <i class="fas fa-box"></i> My Orders
                </a>
                <a href="wishlist.php" class="menu-list-item">
                    <i class="fas fa-heart"></i> Favorited Wishlist
                </a>
                <a href="notifications.php" class="menu-list-item d-flex align-items-center justify-content-between">
                    <span><i class="fas fa-bell me-2"></i> Notifications</span>
                    <?php if ($unread_notifs > 0): ?>
                        <span class="badge bg-danger rounded-pill"><?php echo $unread_notifs; ?></span>
                    <?php endif; ?>
                </a>
                <a href="logout.php" class="menu-list-item text-danger">
                    <i class="fas fa-sign-out-alt"></i> Sign Out
                </a>
            </div>
        </aside>

        <!-- Orders Table Column -->
        <main class="card border-0 shadow-sm p-4" style="border-radius:20px; background:var(--bg-secondary); border:1px solid var(--border-color);">
            <h5 class="fw-bold mb-4"><i class="fas fa-receipt text-primary me-2"></i>Order Receipts & Tracking</h5>

            <?php if (empty($orders)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-box-open fa-4x text-muted mb-4 opacity-30"></i>
                    <h5 class="fw-bold">No orders recorded</h5>
                    <p class="text-secondary small mb-4">You have not completed any shopping checkout yet.</p>
                    <a href="shop.php" class="btn btn-primary" style="border-radius:50px;">Shop Storefront</a>
                </div>
            <?php else: ?>
                <div class="accordion" id="ordersAccordion">
                    <?php foreach ($orders as $index => $ord): ?>
                        <?php 
                        // Fetch order items for this order
                        $order_items = [];
                        try {
                            $items_stmt = $pdo->prepare("SELECT oi.price, oi.quantity, p.id AS product_id, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                            $items_stmt->execute([$ord['id']]);
                            $order_items = $items_stmt->fetchAll();
                        } catch (PDOException $e) {
                            $order_items = [];
                        }

                        $statuses = [
                            'pending' => 'bg-warning-subtle text-warning border border-warning-subtle',
                            'processing' => 'bg-info-subtle text-info border border-info-subtle',
                            'shipped' => 'bg-primary-subtle text-primary border border-primary-subtle',
                            'delivered' => 'bg-success-subtle text-success border border-success-subtle',
                            'cancelled' => 'bg-danger-subtle text-danger border border-danger-subtle',
                        ];
                        $badge_class = $statuses[$ord['status']] ?? 'bg-secondary-subtle text-secondary';
                        ?>
                        
                        <div class="accordion-item border border-light mb-3 shadow-sm" style="border-radius: 12px; overflow:hidden; background: var(--bg-primary);">
                            <h2 class="accordion-header" id="heading-<?php echo $ord['id']; ?>">
                                <button class="accordion-button collapsed py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-<?php echo $ord['id']; ?>" aria-expanded="false" aria-controls="collapse-<?php echo $ord['id']; ?>" style="background:var(--bg-secondary); color:var(--text-primary); box-shadow:none;">
                                    <div class="d-flex align-items-center gap-4">
                                        <span class="fw-bold text-primary">#<?php echo $ord['id']; ?></span>
                                        <span class="small text-muted"><i class="far fa-calendar-alt me-1"></i><?php echo date('M d, Y H:i', strtotime($ord['created_at'])); ?></span>
                                    </div>
                                    <div class="d-flex align-items-center gap-4 me-3">
                                        <span class="fw-bold"><?php echo format_price($ord['total']); ?></span>
                                        <span class="badge <?php echo $badge_class; ?>" style="border-radius:50px; padding:6px 14px; text-transform:capitalize; font-weight:600; font-size:0.75rem;">
                                            <?php echo htmlspecialchars($ord['status']); ?>
                                        </span>
                                    </div>
                                </button>
                            </h2>
                            <div id="collapse-<?php echo $ord['id']; ?>" class="accordion-collapse collapse" aria-labelledby="heading-<?php echo $ord['id']; ?>" data-bs-parent="#ordersAccordion">
                                <div class="accordion-body p-4" style="background: var(--bg-secondary); border-top:1px solid var(--border-color);">
                                    <div class="row g-4">
                                        <!-- Purchased Products -->
                                        <div class="col-md-7">
                                            <h6 class="fw-bold mb-3 small text-muted text-uppercase" style="letter-spacing:0.5px;">Purchased Items</h6>
                                            <div class="d-flex flex-column gap-3">
                                                <?php foreach ($order_items as $item): ?>
                                                    <div class="d-flex align-items-center justify-content-between border-bottom pb-2">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <img src="<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : 'https://via.placeholder.com/50'; ?>" alt="Product" class="rounded" style="width:45px; height:45px; object-fit:cover; border:1px solid var(--border-color);">
                                                            <div>
                                                                <h6 class="fw-bold mb-0 small"><a href="product.php?id=<?php echo $item['product_id']; ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($item['name']); ?></a></h6>
                                                                <span class="text-secondary" style="font-size:0.78rem;">Qty: <?php echo $item['quantity']; ?> @ <?php echo format_price($item['price']); ?></span>
                                                            </div>
                                                        </div>
                                                        <span class="fw-bold small"><?php echo format_price($item['price'] * $item['quantity']); ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        
                                        <!-- Delivery Billing Details -->
                                        <div class="col-md-5" style="border-left: 1px solid var(--border-color);">
                                            <h6 class="fw-bold mb-3 small text-muted text-uppercase" style="letter-spacing:0.5px;">Fulfillment Details</h6>
                                            <ul class="list-unstyled d-flex flex-column gap-2 small">
                                                <li><strong>Recipient:</strong> <?php echo htmlspecialchars($ord['full_name']); ?></li>
                                                <li><strong>Phone:</strong> <?php echo htmlspecialchars($ord['phone']); ?></li>
                                                <li><strong>Mail:</strong> <?php echo htmlspecialchars($ord['email']); ?></li>
                                                <li><strong>Address:</strong> <span class="text-muted d-block mt-1"><?php echo htmlspecialchars($ord['address']); ?></span></li>
                                                <li><strong>Payment Method:</strong> <span class="badge bg-secondary-subtle text-secondary py-1 px-2 border" style="border-radius:4px;"><?php echo htmlspecialchars($ord['payment_method']); ?></span></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
