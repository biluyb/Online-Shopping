<?php
/**
 * =====================================================
 * Online Shopping Registration System — Header
 * =====================================================
 */

// ── Core dependencies ──────────────────────────────
if (!function_exists('is_logged_in')) {
    require_once __DIR__ . '/functions.php';
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/lang.php';

// Language
if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}
$lang = $_SESSION['lang'] ?? 'en';
loadLanguage($lang);

// ── Navbar data ────────────────────────────────────
$cart_count        = 0;
$notification_count = 0;
$site_name = get_setting($conn, 'site_name') ?? 'ShopVerse';

if (is_logged_in()) {
    $cart_count         = get_cart_count($conn, $_SESSION['user_id']);
    $notification_count = get_unread_notifications($conn, $_SESSION['user_id']);
}

// Categories for dropdown
$nav_categories = [];
$cat_q = mysqli_query($conn, "SELECT name, slug, icon FROM categories ORDER BY name ASC LIMIT 10");
if ($cat_q) {
    while ($c = mysqli_fetch_assoc($cat_q)) {
        $nav_categories[] = $c;
    }
}

$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang) ?>" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars(get_setting($conn, 'site_description') ?? 'Premium Online Shopping') ?>">
    <meta name="csrf-token" content="<?= generate_csrf() ?>">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) . ' | ' . htmlspecialchars($site_name) : htmlspecialchars($site_name) ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="assets/images/favicon.png">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
          integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
          crossorigin="anonymous" referrerpolicy="no-referrer">

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
          rel="stylesheet"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
          crossorigin="anonymous">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="css/responsive.css">

    <!-- Navbar inline overrides (slim compact bar) -->
    <style>
        .site-nav { padding: 0 !important; height: 58px; }
        .site-nav .container { height: 100%; }
        .brand-wrap { display:flex; align-items:center; gap:7px; text-decoration:none; }
        .brand-wrap .b-icon {
            width:32px; height:32px; border-radius:9px;
            background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
            display:flex; align-items:center; justify-content:center;
            color:#fff; font-size:.9rem; flex-shrink:0;
        }
        .brand-wrap .b-text {
            font-family: var(--font-heading);
            font-weight:800; font-size:1.15rem;
            background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
            line-height:1;
        }
        /* Nav links */
        .nav-pills-row { display:flex; align-items:center; gap:2px; }
        .nav-pills-row .np-link {
            font-family: var(--font-heading);
            font-weight:500; font-size:.88rem;
            color: var(--text-secondary);
            padding: 5px 13px; border-radius:50px;
            transition: var(--transition-fast);
            text-decoration:none; white-space:nowrap;
        }
        .nav-pills-row .np-link:hover,
        .nav-pills-row .np-link.active {
            color: var(--color-primary);
            background: rgba(var(--color-primary-rgb),.09);
        }
        /* Dropdown */
        .np-dd { position:relative; }
        .np-dd .np-link { cursor:pointer; border:none; background:none; }
        .np-dd-menu {
            display:none; position:absolute; top:calc(100% + 6px); left:0;
            min-width:190px; background: var(--bg-secondary);
            border:1px solid var(--border-color); border-radius:var(--radius-md);
            box-shadow: var(--shadow-md); padding:6px; z-index:999;
        }
        .np-dd:hover .np-dd-menu { display:block; animation: dropIn .2s ease; }
        .np-dd-menu a {
            display:flex; align-items:center; gap:8px;
            padding:8px 12px; border-radius:var(--radius-sm);
            font-size:.86rem; color:var(--text-secondary);
            text-decoration:none; transition:var(--transition-fast);
        }
        .np-dd-menu a:hover { background:rgba(var(--color-primary-rgb),.08); color:var(--color-primary); }
        /* Right icons */
        .nav-actions { display:flex; align-items:center; gap:4px; }
        .nav-ic {
            width:36px; height:36px; border-radius:50%;
            display:flex; align-items:center; justify-content:center;
            color:var(--text-secondary); font-size:.95rem;
            background:transparent; border:none; cursor:pointer;
            transition:var(--transition-fast); position:relative;
            text-decoration:none;
        }
        .nav-ic:hover { color:var(--color-primary); background:rgba(var(--color-primary-rgb),.09); }
        .nav-ic .ic-badge {
            position:absolute; top:2px; right:2px;
            width:16px; height:16px; border-radius:50%;
            background:var(--color-accent); color:#fff;
            font-size:.58rem; font-weight:700;
            display:flex; align-items:center; justify-content:center;
            box-shadow: 0 0 0 2px var(--bg-secondary);
        }
        /* Search bar */
        .nav-search-wrap { position:relative; }
        .nav-search-input {
            width:0; border:none; background:transparent; outline:none;
            transition: width .3s ease, padding .3s ease, border .3s ease;
            font-family: var(--font-body); font-size:.85rem;
            border-radius:50px; color:var(--text-primary);
            padding:0;
        }
        .nav-search-wrap.open .nav-search-input {
            width:180px; padding:5px 14px;
            background:var(--bg-secondary); border:1.5px solid var(--border-color);
        }
        /* Avatar */
        .nav-avatar { width:30px; height:30px; border-radius:50%; object-fit:cover; border:2px solid var(--color-primary); }
        /* User dropdown */
        .user-dd { position:relative; }
        .user-dd-menu {
            display:none; position:absolute; top:calc(100% + 8px); right:0;
            min-width:200px; background:var(--bg-secondary);
            border:1px solid var(--border-color); border-radius:var(--radius-md);
            box-shadow:var(--shadow-md); padding:8px; z-index:999;
        }
        .user-dd:hover .user-dd-menu { display:block; animation:dropIn .2s ease; }
        .user-dd-menu .dd-header {
            padding:10px 12px 8px; border-bottom:1px solid var(--border-color);
            margin-bottom:6px;
        }
        .user-dd-menu .dd-header strong { font-size:.9rem; display:block; }
        .user-dd-menu .dd-header small { color:var(--text-muted); font-size:.78rem; }
        .user-dd-menu a {
            display:flex; align-items:center; gap:10px;
            padding:8px 12px; border-radius:var(--radius-sm);
            font-size:.86rem; color:var(--text-secondary);
            text-decoration:none; transition:var(--transition-fast);
        }
        .user-dd-menu a:hover { background:rgba(var(--color-primary-rgb),.08); color:var(--color-primary); }
        .user-dd-menu a.danger { color:var(--color-accent); }
        .user-dd-menu a.danger:hover { background:rgba(244,63,94,.08); }
        /* Divider */
        .user-dd-menu .dd-div { height:1px; background:var(--border-color); margin:6px 0; }
        /* Lang dropdown */
        .lang-dd { position:relative; }
        .lang-dd-menu {
            display:none; position:absolute; top:calc(100% + 8px); right:0;
            background:var(--bg-secondary); border:1px solid var(--border-color);
            border-radius:var(--radius-md); box-shadow:var(--shadow-md);
            padding:6px; z-index:999; min-width:130px;
        }
        .lang-dd:hover .lang-dd-menu { display:block; animation:dropIn .2s ease; }
        .lang-dd-menu a {
            display:flex; align-items:center; gap:8px;
            padding:7px 12px; border-radius:var(--radius-sm);
            font-size:.84rem; color:var(--text-secondary);
            text-decoration:none; transition:var(--transition-fast);
        }
        .lang-dd-menu a:hover { background:rgba(var(--color-primary-rgb),.08); color:var(--color-primary); }
        /* Mobile toggle */
        .mobile-burger {
            width:36px; height:36px; border-radius:50%;
            display:flex; flex-direction:column;
            align-items:center; justify-content:center; gap:4px;
            background:transparent; border:none; cursor:pointer;
            transition:var(--transition-fast);
        }
        .mobile-burger span { width:18px; height:2px; background:var(--text-secondary); border-radius:2px; transition:var(--transition-fast); }
        .mobile-burger:hover span { background:var(--color-primary); }
        /* Theme toggle */
        .theme-toggle-btn {
            width:36px; height:36px; border-radius:50%;
            background:transparent; border:none; cursor:pointer;
            color:var(--text-secondary); font-size:.95rem;
            display:flex; align-items:center; justify-content:center;
            transition:var(--transition-fast);
        }
        .theme-toggle-btn:hover { color:var(--color-primary); background:rgba(var(--color-primary-rgb),.09); }
        [data-bs-theme="dark"] .icon-sun  { display:inline-block; }
        [data-bs-theme="dark"] .icon-moon { display:none; }
        [data-bs-theme="light"] .icon-sun  { display:none; }
        [data-bs-theme="light"] .icon-moon { display:inline-block; }
        /* Auth buttons */
        .btn-nav-login {
            font-family:var(--font-heading); font-weight:600; font-size:.82rem;
            padding:5px 16px; border-radius:50px;
            border:1.5px solid var(--color-primary); color:var(--color-primary);
            background:transparent; text-decoration:none; transition:var(--transition-fast);
        }
        .btn-nav-login:hover { background:var(--color-primary); color:#fff; }
        .btn-nav-register {
            font-family:var(--font-heading); font-weight:600; font-size:.82rem;
            padding:5px 16px; border-radius:50px;
            background:linear-gradient(135deg,var(--color-primary),var(--color-primary-dark));
            color:#fff; text-decoration:none; border:none;
            box-shadow:0 3px 10px rgba(var(--color-primary-rgb),.3);
            transition:var(--transition-fast);
        }
        .btn-nav-register:hover { transform:translateY(-1px); box-shadow:0 5px 14px rgba(var(--color-primary-rgb),.4); color:#fff; }
    </style>
</head>
<body>

<!-- Loading overlay -->
<div id="loading-overlay" class="loading-overlay">
    <div class="spinner-wrapper">
        <div class="spinner-border" role="status" style="color:var(--color-primary);">
            <span class="visually-hidden">Loading…</span>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     COMPACT NAVBAR
     ══════════════════════════════════════════════════════ -->
<nav class="glassmorphism-nav site-nav fixed-top">
    <div class="container d-flex align-items-center justify-content-between">

        <!-- Brand -->
        <a href="index.php" class="brand-wrap">
            <div class="b-icon"><i class="fas fa-shopping-bag"></i></div>
            <span class="b-text"><?= htmlspecialchars($site_name) ?></span>
        </a>

        <!-- Desktop nav links (hidden on mobile) -->
        <div class="nav-pills-row d-none d-lg-flex">
            <a href="index.php" class="np-link <?= $current_page === 'index' ? 'active' : '' ?>">
                Home
            </a>
            <a href="shop.php" class="np-link <?= $current_page === 'shop' ? 'active' : '' ?>">
                Shop
            </a>

            <!-- Categories dropdown -->
            <div class="np-dd">
                <button class="np-link <?= $current_page === 'category' || $current_page === 'categories' ? 'active' : '' ?>">
                    Categories <i class="fas fa-chevron-down ms-1" style="font-size:.65rem;"></i>
                </button>
                <div class="np-dd-menu">
                    <?php foreach ($nav_categories as $cat): ?>
                    <a href="category.php?slug=<?= htmlspecialchars($cat['slug']) ?>">
                        <i class="<?= htmlspecialchars($cat['icon']) ?>" style="width:14px;"></i>
                        <?= htmlspecialchars($cat['name']) ?>
                    </a>
                    <?php endforeach; ?>
                    <div style="height:1px;background:var(--border-color);margin:4px 0;"></div>
                    <a href="categories.php"><i class="fas fa-border-all" style="width:14px;"></i> All Categories</a>
                </div>
            </div>

            <a href="about.php" class="np-link <?= $current_page === 'about' ? 'active' : '' ?>">
                About
            </a>
            <a href="contact.php" class="np-link <?= $current_page === 'contact' ? 'active' : '' ?>">
                Contact
            </a>
        </div>

        <!-- Right action icons -->
        <div class="nav-actions">

            <!-- Search -->
            <div class="nav-search-wrap" id="navSearchWrap">
                <form action="shop.php" method="GET" class="d-flex align-items-center">
                    <input type="text" name="search" class="nav-search-input" id="navSearchInput"
                           placeholder="Search…" autocomplete="off" aria-label="Search">
                </form>
                <button class="nav-ic" id="searchToggleBtn" type="button" aria-label="Search">
                    <i class="fas fa-search"></i>
                </button>
            </div>

            <!-- Theme toggle -->
            <button class="theme-toggle-btn" id="themeToggleBtn" aria-label="Toggle theme" title="Toggle dark/light">
                <i class="fas fa-moon icon-moon"></i>
                <i class="fas fa-sun icon-sun"></i>
            </button>

            <!-- Language -->
            <div class="lang-dd">
                <button class="nav-ic" aria-label="Language"><i class="fas fa-globe"></i></button>
                <div class="lang-dd-menu">
                    <a href="?lang=en"><i class="fas fa-circle-dot" style="font-size:.7rem;"></i> English</a>
                    <a href="?lang=am"><i class="fas fa-circle-dot" style="font-size:.7rem;"></i> አማርኛ</a>
                </div>
            </div>

            <!-- Cart -->
            <a href="cart.php" class="nav-ic" aria-label="Cart">
                <i class="fas fa-shopping-cart"></i>
                <?php if ($cart_count > 0): ?>
                <span class="ic-badge"><?= $cart_count ?></span>
                <?php endif; ?>
            </a>

            <?php if (is_logged_in()): ?>
            <!-- Notifications -->
            <a href="notifications.php" class="nav-ic" aria-label="Notifications">
                <i class="fas fa-bell"></i>
                <?php if ($notification_count > 0): ?>
                <span class="ic-badge"><?= $notification_count ?></span>
                <?php endif; ?>
            </a>

            <!-- User dropdown -->
            <div class="user-dd">
                <button class="nav-ic" style="width:auto; padding:0;" aria-label="User menu">
                    <img src="uploads/avatars/<?= htmlspecialchars($_SESSION['user_avatar'] ?? 'default.png') ?>"
                         alt="Avatar" class="nav-avatar">
                </button>
                <div class="user-dd-menu">
                    <div class="dd-header">
                        <strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></strong>
                        <small><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></small>
                    </div>
                    <a href="dashboard.php"><i class="fas fa-gauge-high" style="width:16px;"></i> Dashboard</a>
                    <a href="profile.php"><i class="fas fa-user" style="width:16px;"></i> My Profile</a>
                    <a href="orders.php"><i class="fas fa-box" style="width:16px;"></i> My Orders</a>
                    <a href="wishlist.php"><i class="fas fa-heart" style="width:16px;"></i> Wishlist</a>
                    <?php if (is_admin()): ?>
                    <div class="dd-div"></div>
                    <a href="admin/index.php" style="color:var(--color-primary);"><i class="fas fa-shield-halved" style="width:16px;"></i> Admin Panel</a>
                    <?php endif; ?>
                    <div class="dd-div"></div>
                    <a href="logout.php" class="danger"><i class="fas fa-right-from-bracket" style="width:16px;"></i> Logout</a>
                </div>
            </div>

            <?php else: ?>
            <!-- Auth buttons -->
            <a href="login.php" class="btn-nav-login d-none d-sm-inline-block">Login</a>
            <a href="register.php" class="btn-nav-register d-none d-md-inline-block">Sign Up</a>
            <?php endif; ?>

            <!-- Mobile hamburger -->
            <button class="mobile-burger d-lg-none" type="button"
                    data-bs-toggle="offcanvas" data-bs-target="#mobileMenu"
                    aria-controls="mobileMenu" aria-label="Open menu">
                <span></span><span></span><span></span>
            </button>

        </div><!-- /.nav-actions -->
    </div><!-- /.container -->
</nav>

<!-- ══════════════════════════════════════════════════════
     MOBILE OFF-CANVAS MENU
     ══════════════════════════════════════════════════════ -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
    <div class="offcanvas-header">
        <a href="index.php" class="brand-wrap">
            <div class="b-icon"><i class="fas fa-shopping-bag"></i></div>
            <span class="b-text"><?= htmlspecialchars($site_name) ?></span>
        </a>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column gap-1">

        <!-- Mobile search -->
        <form action="shop.php" method="GET" class="mb-3">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" name="search" placeholder="Search products…" aria-label="Search">
            </div>
        </form>

        <!-- Mobile links -->
        <ul class="nav flex-column mobile-nav-links">
            <li class="nav-item"><a class="nav-link <?= $current_page==='index'?'active':'' ?>" href="index.php"><i class="fas fa-home me-2"></i>Home</a></li>
            <li class="nav-item"><a class="nav-link <?= $current_page==='shop'?'active':'' ?>"  href="shop.php"><i class="fas fa-store me-2"></i>Shop</a></li>
            <li class="nav-item"><a class="nav-link <?= $current_page==='categories'?'active':'' ?>" href="categories.php"><i class="fas fa-th-large me-2"></i>Categories</a></li>
            <li class="nav-item"><a class="nav-link <?= $current_page==='about'?'active':'' ?>"   href="about.php"><i class="fas fa-info-circle me-2"></i>About</a></li>
            <li class="nav-item"><a class="nav-link <?= $current_page==='contact'?'active':'' ?>" href="contact.php"><i class="fas fa-envelope me-2"></i>Contact</a></li>
            <li class="nav-item">
                <a class="nav-link <?= $current_page==='cart'?'active':'' ?>" href="cart.php">
                    <i class="fas fa-shopping-cart me-2"></i>Cart
                    <?php if ($cart_count > 0): ?><span class="badge bg-primary rounded-pill ms-1"><?= $cart_count ?></span><?php endif; ?>
                </a>
            </li>
        </ul>

        <hr>

        <?php if (is_logged_in()): ?>
        <!-- Mobile user section -->
        <div class="d-flex align-items-center gap-2 mb-3">
            <img src="uploads/avatars/<?= htmlspecialchars($_SESSION['user_avatar'] ?? 'default.png') ?>"
                 alt="Avatar" width="40" height="40" class="rounded-circle" style="object-fit:cover; border:2px solid var(--color-primary);">
            <div>
                <strong style="font-size:.9rem;"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></strong>
                <small class="d-block text-muted" style="font-size:.75rem;"><?= htmlspecialchars($_SESSION['user_email'] ?? '') ?></small>
            </div>
        </div>
        <ul class="nav flex-column mobile-nav-links">
            <li><a class="nav-link" href="dashboard.php"><i class="fas fa-gauge-high me-2"></i>Dashboard</a></li>
            <li><a class="nav-link" href="profile.php"><i class="fas fa-user me-2"></i>My Profile</a></li>
            <li><a class="nav-link" href="orders.php"><i class="fas fa-box me-2"></i>My Orders</a></li>
            <li><a class="nav-link" href="wishlist.php"><i class="fas fa-heart me-2"></i>Wishlist</a></li>
            <li><a class="nav-link" href="notifications.php">
                <i class="fas fa-bell me-2"></i>Notifications
                <?php if ($notification_count > 0): ?><span class="badge bg-danger rounded-pill ms-1"><?= $notification_count ?></span><?php endif; ?>
            </a></li>
            <?php if (is_admin()): ?>
            <li><a class="nav-link" href="admin/index.php" style="color:var(--color-primary);"><i class="fas fa-shield-halved me-2"></i>Admin Panel</a></li>
            <?php endif; ?>
            <li><a class="nav-link" href="logout.php" style="color:var(--color-accent);"><i class="fas fa-right-from-bracket me-2"></i>Logout</a></li>
        </ul>
        <?php else: ?>
        <div class="d-grid gap-2">
            <a href="login.php" class="btn btn-outline-primary" style="border-radius:50px;"><i class="fas fa-sign-in-alt me-1"></i>Login</a>
            <a href="register.php" class="btn btn-primary" style="border-radius:50px;"><i class="fas fa-user-plus me-1"></i>Sign Up</a>
        </div>
        <?php endif; ?>

        <!-- Mobile theme toggle -->
        <div class="mt-auto pt-3 text-center">
            <button class="theme-toggle-btn w-100 d-flex align-items-center justify-content-center gap-2" id="themeToggleMobile">
                <i class="fas fa-moon icon-moon"></i>
                <i class="fas fa-sun icon-sun"></i>
                <span id="themeLabel" style="font-size:.84rem; font-family:var(--font-heading); font-weight:500;">Dark Mode</span>
            </button>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     Main content wrapper (closed in footer.php)
     ══════════════════════════════════════════════════════ -->
<main class="main-content">

<script>
// ── Theme Toggle ──────────────────────────────────
(function(){
    const saved = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-bs-theme', saved);
    document.documentElement.querySelector('html').setAttribute('data-bs-theme', saved);
})();

document.addEventListener('DOMContentLoaded', function(){
    const html = document.documentElement;

    function applyTheme(t){
        html.setAttribute('data-bs-theme', t);
        localStorage.setItem('theme', t);
        const lbl = document.getElementById('themeLabel');
        if(lbl) lbl.textContent = t==='dark' ? 'Light Mode' : 'Dark Mode';
    }

    // Apply saved theme
    applyTheme(localStorage.getItem('theme') || 'light');

    ['themeToggleBtn','themeToggleMobile'].forEach(function(id){
        const btn = document.getElementById(id);
        if(btn){ btn.addEventListener('click', function(){
            applyTheme(html.getAttribute('data-bs-theme')==='dark' ? 'light' : 'dark');
        }); }
    });

    // ── Search toggle ─────────────────────────────
    const searchWrap  = document.getElementById('navSearchWrap');
    const searchInput = document.getElementById('navSearchInput');
    const searchBtn   = document.getElementById('searchToggleBtn');
    if(searchBtn){
        searchBtn.addEventListener('click', function(e){
            e.stopPropagation();
            searchWrap.classList.toggle('open');
            if(searchWrap.classList.contains('open')) searchInput.focus();
        });
        document.addEventListener('click', function(e){
            if(searchWrap && !searchWrap.contains(e.target)) searchWrap.classList.remove('open');
        });
    }

    // ── Loading overlay ───────────────────────────
    const overlay = document.getElementById('loading-overlay');
    if(overlay) setTimeout(function(){ overlay.classList.add('hidden'); }, 350);

    // ── Navbar scroll compact ────────────────────
    const nav = document.querySelector('.glassmorphism-nav');
    if(nav){
        window.addEventListener('scroll', function(){
            nav.classList.toggle('scrolled', window.scrollY > 40);
        }, { passive: true });
    }
});
</script>
