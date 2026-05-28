<?php
/**
 * Online shopping registration system - Customer Profile Dashboard
 */

require_once 'includes/auth_check.php';

$user_id = $_SESSION['user_id'];

// ── Fetch Spendings & Activity Metrics ──────────────
$total_spent = 0.0;
$total_orders = 0;
$wishlist_count = 0;
$unread_notifs = 0;

try {
    // 1. Total spent and orders
    $stmt = $pdo->prepare("SELECT COUNT(*) AS order_count, COALESCE(SUM(total), 0) AS total_spent FROM orders WHERE user_id = ? AND status != 'cancelled'");
    $stmt->execute([$user_id]);
    $order_data = $stmt->fetch();
    $total_orders = (int)$order_data['order_count'];
    $total_spent = (float)$order_data['total_spent'];

    // 2. Wishlisted items count
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM wishlist WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $wishlist_count = (int)$stmt->fetch()['total'];

    // 3. Unread Notifications
    $stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$user_id]);
    $unread_notifs = (int)$stmt->fetch()['total'];

    // 4. Fetch Recent Orders (Max 5)
    $recent_orders = [];
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
    $stmt->execute([$user_id]);
    $recent_orders = $stmt->fetchAll();
} catch (PDOException $e) {
    // Graceful fail
}

$page_title = "Customer Account Dashboard";
require_once 'includes/header.php';
?>

<!-- ── Custom styles linking ── -->
<link rel="stylesheet" href="css/dashboard.css">

<!-- ══════════════════════════════════════════════════════
     Breadcrumbs
     ══════════════════════════════════════════════════════ -->
<div class="py-4 mb-4" style="background: linear-gradient(135deg, rgba(124, 58, 237, 0.03) 0%, rgba(99, 102, 241, 0.03) 100%); border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <h1 class="fw-bold mb-2">My Account</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
        </nav>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     Dashboard Split Layout Grid
     ══════════════════════════════════════════════════════ -->
<div class="container py-4">
    <!-- Alerts -->
    <?php display_flash('dashboard'); ?>

    <div class="dashboard-grid">
        
        <!-- Left Sidebar Navigation Column -->
        <aside class="d-flex flex-column gap-4">
            <!-- User summary Profile Card -->
            <div class="user-profile-card">
                <div class="avatar-wrapper mb-3">
                    <img src="uploads/avatars/<?php echo htmlspecialchars($_SESSION['user_avatar'] ?? 'default.png'); ?>" alt="Avatar" class="avatar-img">
                </div>
                <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($_SESSION['user_name']); ?></h5>
                <p class="text-secondary small mb-0"><?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
                <span class="badge bg-secondary-subtle text-secondary mt-2 small" style="border-radius:50px; font-weight:600;">Standard Profile</span>
            </div>

            <!-- Dashboard Nav List Group -->
            <div class="menu-list-group">
                <a href="dashboard.php" class="menu-list-item active">
                    <i class="fas fa-tachometer-alt"></i> Dashboard Overview
                </a>
                <a href="profile.php" class="menu-list-item">
                    <i class="fas fa-user-cog"></i> Profile Settings
                </a>
                <a href="orders.php" class="menu-list-item">
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

        <!-- Right Content Column -->
        <main class="d-flex flex-column gap-4">
            <!-- Greeting Box -->
            <div class="card border-0 shadow-sm p-4 text-white d-flex align-items-center justify-content-between flex-row" style="border-radius:20px; background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);">
                <div>
                    <h3 class="fw-bold mb-1">Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h3>
                    <p class="mb-0 opacity-80 small">Welcome to your Online shopping registration system personal workspace. Track orders, manage profile parameters, and read notifications.</p>
                </div>
                <div class="d-none d-md-block fs-1 opacity-20 me-2"><i class="fas fa-hand-peace"></i></div>
            </div>

            <!-- Four Metric Cards -->
            <div class="row g-3">
                <div class="col-sm-6 col-xl-3">
                    <div class="metric-card">
                        <div class="metric-icon sales"><i class="fas fa-coins"></i></div>
                        <div class="metric-info">
                            <h4><?php echo format_price($total_spent); ?></h4>
                            <p>Total Spent</p>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="metric-card">
                        <div class="metric-icon orders"><i class="fas fa-shopping-bag"></i></div>
                        <div class="metric-info">
                            <h4><?php echo $total_orders; ?></h4>
                            <p>Orders Placed</p>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="metric-card">
                        <div class="metric-icon wishlist"><i class="fas fa-heart"></i></div>
                        <div class="metric-info">
                            <h4><?php echo $wishlist_count; ?></h4>
                            <p>Wishlist Count</p>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="metric-card">
                        <div class="metric-icon"><i class="fas fa-bell"></i></div>
                        <div class="metric-info">
                            <h4><?php echo $unread_notifs; ?></h4>
                            <p>Unread Alerts</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders Card List -->
            <div class="card border-0 shadow-sm p-4" style="border-radius:20px; background:var(--bg-secondary); border:1px solid var(--border-color);">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h5 class="fw-bold mb-0"><i class="fas fa-box text-primary me-2"></i>Recent Transactions</h5>
                    <a href="orders.php" class="btn btn-outline-primary btn-sm" style="border-radius:50px;">View All</a>
                </div>

                <?php if (empty($recent_orders)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-box-open fa-3x text-muted mb-3 opacity-30"></i>
                        <h6 class="fw-bold">No orders placed yet</h6>
                        <p class="text-secondary small mb-0">Browse our product catalog to make your first purchase!</p>
                        <a href="shop.php" class="btn btn-primary btn-sm mt-3" style="border-radius:50px;">Shop Catalog</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead>
                                <tr class="text-muted" style="font-size:0.82rem; text-transform:uppercase;">
                                    <th scope="col" style="border-bottom:none;">Order ID</th>
                                    <th scope="col" style="border-bottom:none;">Date</th>
                                    <th scope="col" style="border-bottom:none;">Total</th>
                                    <th scope="col" style="border-bottom:none;">Payment</th>
                                    <th scope="col" style="border-bottom:none;">Status</th>
                                    <th scope="col" style="border-bottom:none; text-align:right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $ord): ?>
                                    <?php 
                                    // Status Badge mapping
                                    $badges = [
                                        'pending' => 'bg-warning-subtle text-warning',
                                        'processing' => 'bg-info-subtle text-info',
                                        'shipped' => 'bg-primary-subtle text-primary',
                                        'delivered' => 'bg-success-subtle text-success',
                                        'cancelled' => 'bg-danger-subtle text-danger',
                                    ];
                                    $badge = $badges[$ord['status']] ?? 'bg-secondary-subtle text-secondary';
                                    ?>
                                    <tr style="border-bottom:1px solid var(--border-color);">
                                        <td class="fw-bold">#<?php echo $ord['id']; ?></td>
                                        <td class="small"><?php echo date('M d, Y', strtotime($ord['created_at'])); ?></td>
                                        <td class="fw-bold"><?php echo format_price($ord['total']); ?></td>
                                        <td class="small"><?php echo htmlspecialchars($ord['payment_method']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $badge; ?>" style="border-radius:50px; font-weight:600; padding:6px 14px; text-transform:capitalize;">
                                                <?php echo htmlspecialchars($ord['status']); ?>
                                            </span>
                                        </td>
                                        <td style="text-align:right;">
                                            <a href="orders.php" class="btn btn-outline-primary btn-sm" style="border-radius:50px;"><i class="fas fa-eye me-1"></i> Details</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
