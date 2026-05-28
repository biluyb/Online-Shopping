<?php
/**
 * Online shopping registration system Admin Header
 * Shared admin layout: doctype through <main class="admin-content">
 */

require_once __DIR__ . '/../includes/functions.php';

// Ensure user is logged in and is admin
if (!is_admin()) {
    redirect('../login.php');
    exit;
}

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token if not present
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get admin user info
$admin_user = $_SESSION['user'] ?? [];
$admin_name = htmlspecialchars($admin_user['full_name'] ?? $admin_user['username'] ?? 'Admin');
$admin_avatar = htmlspecialchars($admin_user['avatar'] ?? '../images/default-avatar.png');

// Determine current page for active nav highlighting
$current_page = basename($_SERVER['PHP_SELF']);

// Fetch unread messages count for notification badge
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE is_read = 0");
    $stmt->execute();
    $unread_messages = $stmt->fetchColumn();
} catch (PDOException $e) {
    $unread_messages = 0;
}

// Fetch pending orders count
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
    $stmt->execute();
    $pending_orders = $stmt->fetchColumn();
} catch (PDOException $e) {
    $pending_orders = 0;
}

$total_notifications = $unread_messages + $pending_orders;

// Flash message handling
$flash_message = '';
$flash_type = '';
if (isset($_SESSION['flash_message'])) {
    $flash_message = $_SESSION['flash_message'];
    $flash_type = $_SESSION['flash_type'] ?? 'success';
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title><?php echo htmlspecialchars($page_title ?? 'Admin Panel'); ?> - Online shopping registration system Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .admin-body { margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #0f1117; color: #e4e6eb; }
        .admin-wrapper { display: flex; min-height: 100vh; }
        .admin-sidebar { width: 260px; background: #1a1d27; border-right: 1px solid #2d3748; position: fixed; top: 0; left: 0; bottom: 0; z-index: 100; transition: transform 0.3s ease; overflow-y: auto; }
        .admin-sidebar .sidebar-logo { padding: 20px 24px; border-bottom: 1px solid #2d3748; display: flex; align-items: center; gap: 12px; }
        .admin-sidebar .sidebar-logo h2 { margin: 0; font-size: 1.3rem; color: #818cf8; }
        .admin-sidebar .sidebar-logo span { font-size: 0.75rem; color: #9ca3af; display: block; }
        .admin-sidebar .sidebar-nav { padding: 16px 0; }
        .admin-sidebar .sidebar-nav .nav-section { padding: 8px 24px 4px; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; color: #6b7280; }
        .admin-sidebar .nav-link { display: flex; align-items: center; gap: 12px; padding: 10px 24px; color: #9ca3af; text-decoration: none; transition: all 0.2s; border-left: 3px solid transparent; font-size: 0.9rem; }
        .admin-sidebar .nav-link:hover { background: rgba(129,140,248,0.08); color: #e4e6eb; }
        .admin-sidebar .nav-link.active { background: rgba(129,140,248,0.12); color: #818cf8; border-left-color: #818cf8; }
        .admin-sidebar .nav-link i { width: 20px; text-align: center; font-size: 1rem; }
        .admin-sidebar .nav-link .badge { margin-left: auto; background: #ef4444; color: #fff; font-size: 0.7rem; padding: 2px 8px; border-radius: 10px; }
        .admin-main { margin-left: 260px; flex: 1; display: flex; flex-direction: column; }
        .admin-topbar { background: #1a1d27; border-bottom: 1px solid #2d3748; padding: 12px 28px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 50; }
        .admin-topbar .topbar-left { display: flex; align-items: center; gap: 16px; }
        .admin-topbar .sidebar-toggle { background: none; border: none; color: #9ca3af; cursor: pointer; font-size: 1.2rem; display: none; }
        .admin-topbar .topbar-search { position: relative; }
        .admin-topbar .topbar-search input { background: #0f1117; border: 1px solid #2d3748; color: #e4e6eb; padding: 8px 16px 8px 36px; border-radius: 8px; font-size: 0.85rem; width: 280px; }
        .admin-topbar .topbar-search i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6b7280; }
        .admin-topbar .topbar-right { display: flex; align-items: center; gap: 20px; }
        .admin-topbar .notif-btn { background: none; border: none; color: #9ca3af; cursor: pointer; font-size: 1.1rem; position: relative; }
        .admin-topbar .notif-btn .notif-badge { position: absolute; top: -6px; right: -6px; background: #ef4444; color: #fff; font-size: 0.6rem; width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .admin-topbar .admin-profile { display: flex; align-items: center; gap: 10px; cursor: pointer; position: relative; }
        .admin-topbar .admin-profile img { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; border: 2px solid #2d3748; }
        .admin-topbar .admin-profile .admin-info span { font-size: 0.85rem; font-weight: 600; }
        .admin-topbar .admin-profile .admin-info small { display: block; font-size: 0.7rem; color: #9ca3af; }
        .admin-topbar .admin-dropdown { position: absolute; top: 100%; right: 0; background: #1a1d27; border: 1px solid #2d3748; border-radius: 8px; padding: 8px; min-width: 180px; display: none; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .admin-topbar .admin-dropdown.show { display: block; }
        .admin-topbar .admin-dropdown a { display: flex; align-items: center; gap: 10px; padding: 8px 12px; color: #9ca3af; text-decoration: none; border-radius: 6px; font-size: 0.85rem; }
        .admin-topbar .admin-dropdown a:hover { background: rgba(129,140,248,0.1); color: #e4e6eb; }
        .admin-content { padding: 28px; flex: 1; }

        /* Reusable admin component styles */
        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 28px; flex-wrap: wrap; gap: 12px; }
        .page-header h1 { font-size: 1.6rem; margin: 0; color: #e4e6eb; }
        .page-header .breadcrumb { font-size: 0.8rem; color: #6b7280; }
        .page-header .breadcrumb a { color: #818cf8; text-decoration: none; }
        .stat-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 28px; }
        .stat-card { background: #1a1d27; border: 1px solid #2d3748; border-radius: 12px; padding: 22px; display: flex; align-items: center; gap: 16px; transition: transform 0.2s, box-shadow 0.2s; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.2); }
        .stat-card .stat-icon { width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }
        .stat-card .stat-icon.revenue { background: rgba(34,197,94,0.15); color: #22c55e; }
        .stat-card .stat-icon.orders { background: rgba(59,130,246,0.15); color: #3b82f6; }
        .stat-card .stat-icon.users { background: rgba(168,85,247,0.15); color: #a855f7; }
        .stat-card .stat-icon.products { background: rgba(234,179,8,0.15); color: #eab308; }
        .stat-card .stat-info h3 { margin: 0 0 4px; font-size: 1.5rem; color: #e4e6eb; }
        .stat-card .stat-info p { margin: 0; font-size: 0.8rem; color: #9ca3af; }
        .stat-card .stat-trend { margin-left: auto; font-size: 0.75rem; padding: 4px 10px; border-radius: 20px; }
        .stat-card .stat-trend.up { background: rgba(34,197,94,0.15); color: #22c55e; }
        .stat-card .stat-trend.down { background: rgba(239,68,68,0.15); color: #ef4444; }
        .admin-card { background: #1a1d27; border: 1px solid #2d3748; border-radius: 12px; padding: 24px; margin-bottom: 24px; }
        .admin-card .card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #2d3748; }
        .admin-card .card-header h3 { margin: 0; font-size: 1.1rem; color: #e4e6eb; }
        .admin-table { width: 100%; border-collapse: collapse; }
        .admin-table th { text-align: left; padding: 12px 16px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; border-bottom: 1px solid #2d3748; background: rgba(0,0,0,0.2); }
        .admin-table td { padding: 12px 16px; border-bottom: 1px solid rgba(45,55,72,0.5); font-size: 0.85rem; color: #d1d5db; }
        .admin-table tr:hover td { background: rgba(129,140,248,0.04); }
        .admin-table .product-thumb { width: 45px; height: 45px; border-radius: 8px; object-fit: cover; border: 1px solid #2d3748; }
        .status-badge { padding: 4px 12px; border-radius: 20px; font-size: 0.72rem; font-weight: 600; text-transform: capitalize; }
        .status-badge.pending { background: rgba(234,179,8,0.15); color: #eab308; }
        .status-badge.processing { background: rgba(59,130,246,0.15); color: #3b82f6; }
        .status-badge.shipped { background: rgba(168,85,247,0.15); color: #a855f7; }
        .status-badge.delivered { background: rgba(34,197,94,0.15); color: #22c55e; }
        .status-badge.cancelled { background: rgba(239,68,68,0.15); color: #ef4444; }
        .status-badge.read { background: rgba(34,197,94,0.15); color: #22c55e; }
        .status-badge.unread { background: rgba(59,130,246,0.15); color: #3b82f6; }
        .btn { display: inline-flex; align-items: center; gap: 8px; padding: 8px 18px; border-radius: 8px; border: none; cursor: pointer; font-size: 0.85rem; font-weight: 500; text-decoration: none; transition: all 0.2s; }
        .btn-primary { background: #818cf8; color: #fff; }
        .btn-primary:hover { background: #6366f1; }
        .btn-success { background: rgba(34,197,94,0.15); color: #22c55e; border: 1px solid rgba(34,197,94,0.3); }
        .btn-success:hover { background: rgba(34,197,94,0.25); }
        .btn-danger { background: rgba(239,68,68,0.15); color: #ef4444; border: 1px solid rgba(239,68,68,0.3); }
        .btn-danger:hover { background: rgba(239,68,68,0.25); }
        .btn-warning { background: rgba(234,179,8,0.15); color: #eab308; border: 1px solid rgba(234,179,8,0.3); }
        .btn-warning:hover { background: rgba(234,179,8,0.25); }
        .btn-sm { padding: 5px 12px; font-size: 0.78rem; }
        .btn-icon { width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: 6px; }
        .admin-form { max-width: 700px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 6px; font-size: 0.85rem; color: #d1d5db; font-weight: 500; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px 14px; background: #0f1117; border: 1px solid #2d3748; border-radius: 8px; color: #e4e6eb; font-size: 0.9rem; transition: border-color 0.2s; box-sizing: border-box; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #818cf8; }
        .form-group textarea { min-height: 120px; resize: vertical; }
        .form-group .form-check { display: flex; align-items: center; gap: 8px; }
        .form-group .form-check input[type="checkbox"] { width: auto; }
        .form-actions { display: flex; gap: 12px; margin-top: 28px; }
        .filter-bar { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; flex-wrap: wrap; }
        .filter-bar input, .filter-bar select { padding: 8px 14px; background: #0f1117; border: 1px solid #2d3748; border-radius: 8px; color: #e4e6eb; font-size: 0.85rem; }
        .filter-tabs { display: flex; gap: 4px; margin-bottom: 24px; flex-wrap: wrap; }
        .filter-tabs a { padding: 8px 18px; border-radius: 8px; color: #9ca3af; text-decoration: none; font-size: 0.85rem; transition: all 0.2s; border: 1px solid transparent; }
        .filter-tabs a:hover { background: rgba(129,140,248,0.08); color: #e4e6eb; }
        .filter-tabs a.active { background: rgba(129,140,248,0.15); color: #818cf8; border-color: rgba(129,140,248,0.3); }
        .pagination { display: flex; align-items: center; justify-content: center; gap: 6px; margin-top: 24px; }
        .pagination a, .pagination span { padding: 8px 14px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; color: #9ca3af; border: 1px solid #2d3748; transition: all 0.2s; }
        .pagination a:hover { background: rgba(129,140,248,0.1); color: #818cf8; border-color: rgba(129,140,248,0.3); }
        .pagination .active { background: #818cf8; color: #fff; border-color: #818cf8; }
        .pagination .disabled { opacity: 0.4; pointer-events: none; }
        .chart-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 24px; margin-bottom: 24px; }
        .chart-container { position: relative; height: 300px; }
        .flash-message { padding: 14px 20px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; animation: slideDown 0.3s ease; }
        .flash-message.success { background: rgba(34,197,94,0.15); border: 1px solid rgba(34,197,94,0.3); color: #22c55e; }
        .flash-message.error { background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #ef4444; }
        .flash-message.warning { background: rgba(234,179,8,0.15); border: 1px solid rgba(234,179,8,0.3); color: #eab308; }
        .toggle-switch { position: relative; width: 50px; height: 26px; }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .toggle-switch .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: #2d3748; border-radius: 26px; transition: 0.3s; }
        .toggle-switch .slider:before { content: ""; position: absolute; height: 20px; width: 20px; left: 3px; bottom: 3px; background: #e4e6eb; border-radius: 50%; transition: 0.3s; }
        .toggle-switch input:checked + .slider { background: #818cf8; }
        .toggle-switch input:checked + .slider:before { transform: translateX(24px); }
        .empty-state { text-align: center; padding: 48px 20px; color: #6b7280; }
        .empty-state i { font-size: 3rem; margin-bottom: 16px; display: block; }
        .empty-state p { font-size: 0.95rem; }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 768px) {
            .admin-sidebar { transform: translateX(-100%); }
            .admin-sidebar.open { transform: translateX(0); }
            .admin-main { margin-left: 0; }
            .admin-topbar .sidebar-toggle { display: block; }
            .admin-topbar .topbar-search { display: none; }
            .stat-cards { grid-template-columns: 1fr 1fr; }
            .chart-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 480px) {
            .stat-cards { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <!-- Admin Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-logo">
                <i class="fas fa-store" style="font-size: 1.5rem; color: #818cf8;"></i>
                <div>
                    <h2>Registration System</h2>
                    <span>Admin Panel</span>
                </div>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-section">Main</div>
                <a href="index.php" class="nav-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="products.php" class="nav-link <?php echo $current_page === 'products.php' ? 'active' : ''; ?>">
                    <i class="fas fa-box"></i> Products
                </a>
                <a href="categories.php" class="nav-link <?php echo $current_page === 'categories.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tags"></i> Categories
                </a>
                <a href="orders.php" class="nav-link <?php echo $current_page === 'orders.php' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart"></i> Orders
                    <?php if ($pending_orders > 0): ?>
                        <span class="badge"><?php echo $pending_orders; ?></span>
                    <?php endif; ?>
                </a>

                <div class="nav-section" style="margin-top: 16px;">Manage</div>
                <a href="users.php" class="nav-link <?php echo $current_page === 'users.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="messages.php" class="nav-link <?php echo $current_page === 'messages.php' ? 'active' : ''; ?>">
                    <i class="fas fa-envelope"></i> Messages
                    <?php if ($unread_messages > 0): ?>
                        <span class="badge"><?php echo $unread_messages; ?></span>
                    <?php endif; ?>
                </a>
                <a href="settings.php" class="nav-link <?php echo $current_page === 'settings.php' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i> Settings
                </a>

                <div class="nav-section" style="margin-top: 16px;">Other</div>
                <a href="../index.php" class="nav-link">
                    <i class="fas fa-arrow-left"></i> Back to Site
                </a>
            </nav>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="admin-main">
            <!-- Top Bar -->
            <header class="admin-topbar">
                <div class="topbar-left">
                    <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle Sidebar">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="topbar-search">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search..." id="adminSearch">
                    </div>
                </div>
                <div class="topbar-right">
                    <button class="notif-btn" id="notifBtn" title="Notifications">
                        <i class="fas fa-bell"></i>
                        <?php if ($total_notifications > 0): ?>
                            <span class="notif-badge"><?php echo $total_notifications; ?></span>
                        <?php endif; ?>
                    </button>
                    <div class="admin-profile" id="adminProfileBtn">
                        <img src="<?php echo $admin_avatar; ?>" alt="Admin Avatar">
                        <div class="admin-info">
                            <span><?php echo $admin_name; ?></span>
                            <small>Administrator</small>
                        </div>
                        <i class="fas fa-chevron-down" style="color: #6b7280; font-size: 0.7rem;"></i>
                        <div class="admin-dropdown" id="adminDropdown">
                            <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                            <a href="../profile.php"><i class="fas fa-user"></i> My Profile</a>
                            <hr style="border-color: #2d3748; margin: 6px 0;">
                            <a href="../logout.php" style="color: #ef4444;"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="admin-content">
                <?php if ($flash_message): ?>
                    <div class="flash-message <?php echo htmlspecialchars($flash_type); ?>">
                        <i class="fas fa-<?php echo $flash_type === 'success' ? 'check-circle' : ($flash_type === 'error' ? 'exclamation-circle' : 'exclamation-triangle'); ?>"></i>
                        <?php echo htmlspecialchars($flash_message); ?>
                    </div>
                <?php endif; ?>
