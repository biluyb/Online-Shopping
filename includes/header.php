<?php
/**
 * =====================================================
 * Online shopping registration system - Header Template
 * =====================================================
 * 
 * Shared header included on every public-facing page.
 * Contains the DOCTYPE, <head>, navigation bar, and
 * loading overlay. Expects <?php
    require_once __DIR__ . '/functions.php';
    // Ensure session is started
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

?> to be loaded.
 * =====================================================
 */

// Ensure core functions are available
if (!function_exists('is_logged_in')) {
    require_once __DIR__ . '/functions.php';
}
<?php
// Ensure session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include language helper
require_once __DIR__ . '/lang.php';

// Language selection handling
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = $_SESSION['lang'] ?? 'en';
loadLanguage($lang);
?>

// ── Fetch dynamic data for the navbar ─────────────────
$cart_count = 0;
$notification_count = 0;
$site_name = get_setting($conn, 'site_name') ?? 'Online shopping registration system';

if (is_logged_in()) {
    $cart_count = get_cart_count($conn, $_SESSION['user_id']);
    $notification_count = get_unread_notifications($conn, $_SESSION['user_id']);
}

// ── Fetch categories for the dropdown ─────────────────
$nav_categories = [];
$cat_query = mysqli_query($conn, "SELECT name, slug, icon FROM categories ORDER BY name ASC");
if ($cat_query) {
    while ($cat = mysqli_fetch_assoc($cat_query)) {
        $nav_categories[] = $cat;
    }
}

// ── Determine the current page for active nav states ──
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <!-- ── Meta Tags ─────────────────────────────────── -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="<?php echo htmlspecialchars(get_setting($conn, 'site_description') ?? 'Premium Online Shopping Experience'); ?>">
    <meta name="author" content="Online shopping registration system">
    <meta name="csrf-token" content="<?php echo generate_csrf(); ?>">

    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' | ' . htmlspecialchars($site_name) : htmlspecialchars($site_name); ?></title>

    <!-- ── Favicon ───────────────────────────────────── -->
    <link rel="icon" type="image/png" href="assets/images/favicon.png">

    <!-- ── Google Fonts ──────────────────────────────── -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- ── Font Awesome 6 ────────────────────────────── -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer">

    <!-- ── Bootstrap 5.3 CSS ─────────────────────────── -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">

    <!-- ── Custom Stylesheets ────────────────────────── -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
</head>
<body>

<!-- ══════════════════════════════════════════════════════
     Loading Overlay
     ══════════════════════════════════════════════════════ -->
<div id="loading-overlay" class="loading-overlay">
    <div class="spinner-wrapper">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     Navigation Bar (Glassmorphism)
     ══════════════════════════════════════════════════════ -->
<nav class="navbar navbar-expand-lg fixed-top glassmorphism-nav">
    <div class="container">

        <!-- ── Logo ──────────────────────────────────── -->
        <a class="navbar-brand d-flex align-items-center" href="index.php">
            <i class="fas fa-shopping-bag brand-icon me-2"></i>
            <span class="brand-text"><?php echo htmlspecialchars($site_name); ?></span>
        </a>

        <!-- ── Mobile Toggle ─────────────────────────── -->
        <button class="navbar-toggler border-0" type="button"
                data-bs-toggle="offcanvas" data-bs-target="#mobileMenu"
                aria-controls="mobileMenu" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- ── Desktop Navigation ────────────────────── -->
        <div class="collapse navbar-collapse" id="navbarMain">

            <!-- Primary Nav Links -->
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'index' ? 'active' : ''; ?>" href="index.php">
                        <i class="fas fa-home me-1"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'shop' ? 'active' : ''; ?>" href="shop.php">
                        <i class="fas fa-store me-1"></i> Shop
                    </a>
                </li>

                <!-- Categories Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo $current_page === 'category' ? 'active' : ''; ?>"
                       href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-th-large me-1"></i> Categories
                    </a>
                    <ul class="dropdown-menu dropdown-menu-animated">
                        <?php foreach ($nav_categories as $cat): ?>
                            <li>
                                <a class="dropdown-item" href="category.php?slug=<?php echo htmlspecialchars($cat['slug']); ?>">
                                    <i class="<?php echo htmlspecialchars($cat['icon']); ?> me-2"></i>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="categories.php">
                                <i class="fas fa-border-all me-2"></i> View All Categories
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page === 'about' ? 'active' : ''; ?>" href="about.php">
                        <i class="fas fa-info-circle me-1"></i> About
                    </a>
                </li>
            </ul>

            <!-- Right-side Icons -->
            <div class="navbar-icons d-flex align-items-center gap-3">

                <!-- Search (Expandable) -->
                <div class="nav-search-wrapper position-relative">
                    <button class="btn btn-link nav-icon-btn search-toggle" type="button" aria-label="Search">
                        <i class="fas fa-search"></i>
                    </button>
                    <form class="nav-search-form" action="shop.php" method="GET">
                        <div class="input-group">
                            <input type="text" class="form-control" name="search"
                                   placeholder="Search products..." autocomplete="off"
                                   aria-label="Search products">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Dark/Light Mode Toggle -->
                <button class="btn btn-link nav-icon-btn theme-toggle" type="button"
                        aria-label="Toggle dark mode" title="Toggle theme">
                    <i class="fas fa-moon theme-icon-dark"></i>
                    <i class="fas fa-sun theme-icon-light d-none"></i>
                </button>
                <!-- Language Switcher -->
                <div class="dropdown">
                    <button class="btn btn-link nav-icon-btn dropdown-toggle language-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Select Language">
                        <i class="fas fa-language"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="?lang=en"><i class="fas fa-flag-usa me-2"></i> English</a></li>
                        <li><a class="dropdown-item" href="?lang=am"><i class="fas fa-flag me-2"></i> አማርኛ</a></li>
                    </ul>
                </div>

                <!-- Cart -->
                <a href="cart.php" class="nav-icon-btn position-relative" aria-label="Cart">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if ($cart_count > 0): ?>
                        <span class="badge-count" id="cart-badge"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>

                <?php if (is_logged_in()): ?>
                    <!-- Notifications Bell -->
                    <a href="notifications.php" class="nav-icon-btn position-relative" aria-label="Notifications">
                        <i class="fas fa-bell"></i>
                        <?php if ($notification_count > 0): ?>
                            <span class="badge-count" id="notification-badge"><?php echo $notification_count; ?></span>
                        <?php endif; ?>
                    </a>

                    <!-- User Dropdown (Logged In) -->
                    <div class="dropdown">
                        <button class="btn btn-link nav-icon-btn dropdown-toggle user-dropdown-toggle"
                                type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="uploads/avatars/<?php echo htmlspecialchars($_SESSION['user_avatar'] ?? 'default.png'); ?>"
                                 alt="Avatar" class="user-avatar-sm rounded-circle"
                                 width="32" height="32">
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-animated user-dropdown">
                            <li class="dropdown-header">
                                <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></strong>
                                <br>
                                <small class="text-muted"><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></small>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="dashboard.php">
                                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="profile.php">
                                    <i class="fas fa-user me-2"></i> My Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="orders.php">
                                    <i class="fas fa-box me-2"></i> My Orders
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="wishlist.php">
                                    <i class="fas fa-heart me-2"></i> Wishlist
                                </a>
                            </li>
                            <?php if (is_admin()): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-primary" href="admin/index.php">
                                        <i class="fas fa-shield-alt me-2"></i> Admin Panel
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>

                <?php else: ?>
                    <!-- Auth Buttons (Not Logged In) -->
                    <a href="login.php" class="btn btn-outline-primary btn-sm nav-auth-btn">
                        <i class="fas fa-sign-in-alt me-1"></i> Login
                    </a>
                    <a href="register.php" class="btn btn-primary btn-sm nav-auth-btn d-none d-md-inline-block">
                        <i class="fas fa-user-plus me-1"></i> Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- ══════════════════════════════════════════════════════
     Mobile Offcanvas Menu
     ══════════════════════════════════════════════════════ -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="mobileMenuLabel">
            <i class="fas fa-shopping-bag me-2"></i><?php echo htmlspecialchars($site_name); ?>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">

        <!-- Mobile Search -->
        <form class="mb-4" action="shop.php" method="GET">
            <div class="input-group">
                <input type="text" class="form-control" name="search"
                       placeholder="Search products..." aria-label="Search products">
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>

        <!-- Mobile Nav Links -->
        <ul class="nav flex-column mobile-nav-links">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'index' ? 'active' : ''; ?>" href="index.php">
                    <i class="fas fa-home me-2"></i> Home
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'shop' ? 'active' : ''; ?>" href="shop.php">
                    <i class="fas fa-store me-2"></i> Shop
                </a>
            </li>

            <!-- Mobile Categories Accordion -->
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#mobileCategoriesCollapse" role="button" aria-expanded="false">
                    <i class="fas fa-th-large me-2"></i> Categories
                    <i class="fas fa-chevron-down float-end"></i>
                </a>
                <div class="collapse" id="mobileCategoriesCollapse">
                    <ul class="nav flex-column ms-3">
                        <?php foreach ($nav_categories as $cat): ?>
                            <li class="nav-item">
                                <a class="nav-link py-2" href="category.php?slug=<?php echo htmlspecialchars($cat['slug']); ?>">
                                    <i class="<?php echo htmlspecialchars($cat['icon']); ?> me-2"></i>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'about' ? 'active' : ''; ?>" href="about.php">
                    <i class="fas fa-info-circle me-2"></i> About
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'cart' ? 'active' : ''; ?>" href="cart.php">
                    <i class="fas fa-shopping-cart me-2"></i> Cart
                    <?php if ($cart_count > 0): ?>
                        <span class="badge bg-primary rounded-pill ms-1"><?php echo $cart_count; ?></span>
                    <?php endif; ?>
                </a>
            </li>
        </ul>

        <hr>

        <!-- Mobile Auth / User Section -->
        <?php if (is_logged_in()): ?>
            <div class="mobile-user-section mb-3">
                <div class="d-flex align-items-center mb-3">
                    <img src="uploads/avatars/<?php echo htmlspecialchars($_SESSION['user_avatar'] ?? 'default.png'); ?>"
                         alt="Avatar" class="rounded-circle me-2" width="40" height="40">
                    <div>
                        <strong><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></strong>
                        <br>
                        <small class="text-muted"><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></small>
                    </div>
                </div>
            </div>
            <ul class="nav flex-column mobile-nav-links">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="profile.php"><i class="fas fa-user me-2"></i> My Profile</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="orders.php"><i class="fas fa-box me-2"></i> My Orders</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="notifications.php">
                        <i class="fas fa-bell me-2"></i> Notifications
                        <?php if ($notification_count > 0): ?>
                            <span class="badge bg-danger rounded-pill ms-1"><?php echo $notification_count; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php if (is_admin()): ?>
                    <li class="nav-item">
                        <a class="nav-link text-primary" href="admin/index.php"><i class="fas fa-shield-alt me-2"></i> Admin Panel</a>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
                </li>
            </ul>
        <?php else: ?>
            <div class="d-grid gap-2">
                <a href="login.php" class="btn btn-outline-primary">
                    <i class="fas fa-sign-in-alt me-1"></i> Login
                </a>
                <a href="register.php" class="btn btn-primary">
                    <i class="fas fa-user-plus me-1"></i> Register
                </a>
            </div>
        <?php endif; ?>

        <!-- Mobile Theme Toggle -->
        <div class="mt-3 text-center">
            <button class="btn btn-sm btn-outline-secondary theme-toggle" type="button">
                <i class="fas fa-moon theme-icon-dark me-1"></i>
                <i class="fas fa-sun theme-icon-light d-none me-1"></i>
                <span class="theme-label-dark">Dark Mode</span>
                <span class="theme-label-light d-none">Light Mode</span>
            </button>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     Main Content Wrapper (opened here, closed in footer)
     ══════════════════════════════════════════════════════ -->
<main class="main-content">
