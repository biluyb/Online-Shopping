<?php
/**
 * Online shopping registration system - User Notification Manager Panel
 */

require_once 'includes/auth_check.php';

$user_id = $_SESSION['user_id'];
$notifications = [];
$has_more = false;

try {
    // Fetch initial 5 notifications
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 6");
    $stmt->execute([$user_id]);
    $fetched = $stmt->fetchAll();
    
    if (count($fetched) > 5) {
        $has_more = true;
        $notifications = array_slice($fetched, 0, 5);
    } else {
        $notifications = $fetched;
    }
    
    // Count unread notifications
    $stmt2 = $pdo->prepare("SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt2->execute([$user_id]);
    $unread_notifs = (int)$stmt2->fetch()['total'];
} catch (PDOException $e) {
    // Fail silently
}

$page_title = "My Account Notifications";
require_once 'includes/header.php';
?>

<!-- Custom styles -->
<link rel="stylesheet" href="css/dashboard.css">

<!-- ══════════════════════════════════════════════════════
     Breadcrumbs
     ══════════════════════════════════════════════════════ -->
<div class="py-4 mb-4" style="background: linear-gradient(135deg, rgba(124, 58, 237, 0.03) 0%, rgba(99, 102, 241, 0.03) 100%); border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <h1 class="fw-bold mb-2">Notifications Manager</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Notifications</li>
            </ol>
        </nav>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     Dashboard Split Grid
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
                <a href="orders.php" class="menu-list-item">
                    <i class="fas fa-box"></i> My Orders
                </a>
                <a href="wishlist.php" class="menu-list-item">
                    <i class="fas fa-heart"></i> Favorited Wishlist
                </a>
                <a href="notifications.php" class="menu-list-item active d-flex align-items-center justify-content-between">
                    <span><i class="fas fa-bell me-2"></i> Notifications</span>
                    <span class="badge bg-danger rounded-pill notification-badge <?php echo ($unread_notifs <= 0) ? 'hidden' : ''; ?>"><?php echo $unread_notifs; ?></span>
                </a>
                <a href="logout.php" class="menu-list-item text-danger">
                    <i class="fas fa-sign-out-alt"></i> Sign Out
                </a>
            </div>
        </aside>

        <!-- Notifications Main Manager Column -->
        <main class="card border-0 shadow-sm p-4" style="border-radius:20px; background:var(--bg-secondary); border:1px solid var(--border-color);">
            <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                <h5 class="fw-bold mb-0"><i class="fas fa-bell text-primary me-2"></i>Alerts Ledger</h5>
                <?php if (!empty($notifications)): ?>
                    <button class="btn btn-outline-primary btn-sm" data-mark-all-read style="border-radius:50px;">
                        <i class="fas fa-check-double me-1"></i> Mark All as Read
                    </button>
                <?php endif; ?>
            </div>

            <?php if (empty($notifications)): ?>
                <div class="text-center py-5">
                    <i class="far fa-bell-slash fa-4x text-muted mb-4 opacity-30"></i>
                    <h5 class="fw-bold">No notifications yet</h5>
                    <p class="text-secondary small mb-0">We will notify you here when transactions or account changes occur.</p>
                </div>
            <?php else: ?>
                <div class="notifications-list mb-4" data-notifications>
                    <?php foreach ($notifications as $n): ?>
                        <div class="notification-item <?php echo $n['is_read'] ? 'read' : ''; ?>" data-notification-id="<?php echo $n['id']; ?>">
                            <div class="notification-icon">
                                <i class="fas <?php echo htmlspecialchars($n['icon'] ?? 'fa-bell'); ?>"></i>
                            </div>
                            <div class="notification-content">
                                <p class="notification-text text-dark"><?php echo htmlspecialchars($n['message']); ?></p>
                                <span class="notification-time"><?php echo time_ago($n['created_at']); ?></span>
                            </div>
                            <button class="notification-delete" data-delete-notification aria-label="Delete">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($has_more): ?>
                    <div class="text-center">
                        <button class="btn btn-outline-primary btn-sm" data-load-more-notifications style="border-radius:50px; padding: 8px 24px;">
                            Load More
                        </button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>
</div>

<!-- Load Dashboard module dependencies -->
<script src="js/dashboard.js"></script>

<?php require_once 'includes/footer.php'; ?>
