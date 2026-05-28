<?php
/**
 * ShopVerse - User Favorited Items Wishlist Catalog
 */

require_once 'includes/auth_check.php';

$user_id = $_SESSION['user_id'];
$wishlist_items = [];

try {
    // Fetch all user wishlisted products
    $stmt = $pdo->prepare("SELECT w.id AS wishlist_id, p.*, c.name AS category_name FROM wishlist w JOIN products p ON w.product_id = p.id LEFT JOIN categories c ON p.category_id = c.id WHERE w.user_id = ? ORDER BY w.created_at DESC");
    $stmt->execute([$user_id]);
    $wishlist_items = $stmt->fetchAll();

    // Fetch unread notifications count
    $stmt2 = $pdo->prepare("SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt2->execute([$user_id]);
    $unread_notifs = (int)$stmt2->fetch()['total'];
} catch (PDOException $e) {
    // Fail gracefully
}

$page_title = "My Wishlist Catalog";
require_once 'includes/header.php';
?>

<!-- Custom styles link -->
<link rel="stylesheet" href="css/dashboard.css">

<!-- ══════════════════════════════════════════════════════
     Breadcrumbs
     ══════════════════════════════════════════════════════ -->
<div class="py-4 mb-4" style="background: linear-gradient(135deg, rgba(124, 58, 237, 0.03) 0%, rgba(99, 102, 241, 0.03) 100%); border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <h1 class="fw-bold mb-2">My Wishlist</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Wishlist</li>
            </ol>
        </nav>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     Dashboard Split Layout
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
                <a href="wishlist.php" class="menu-list-item active">
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

        <!-- Wishlisted Items Grid -->
        <main class="card border-0 shadow-sm p-4" style="border-radius:20px; background:var(--bg-secondary); border:1px solid var(--border-color);">
            <h5 class="fw-bold mb-4"><i class="fas fa-heart text-primary me-2"></i>My Saved Items</h5>

            <?php if (empty($wishlist_items)): ?>
                <div class="text-center py-5">
                    <i class="far fa-heart fa-4x text-muted mb-4 opacity-30"></i>
                    <h5 class="fw-bold">Your wishlist is empty</h5>
                    <p class="text-secondary small mb-4">Explore our categories and catalog to save items you love.</p>
                    <a href="shop.php" class="btn btn-primary" style="border-radius:50px;">Go to Shop</a>
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
                    <?php foreach ($wishlist_items as $p): ?>
                        <?php 
                        $has_sale = !empty($p['sale_price']) && $p['sale_price'] > 0;
                        $image_url = !empty($p['image']) ? htmlspecialchars($p['image']) : 'https://via.placeholder.com/400x300';
                        ?>
                        <div class="col" id="wishlist-item-<?php echo $p['id']; ?>">
                            <div class="card h-100 product-card">
                                <?php if ($has_sale): ?>
                                    <span class="product-badge">SALE</span>
                                <?php endif; ?>
                                
                                <div class="product-image-wrapper">
                                    <img src="<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                                    <div class="product-overlay">
                                        <a href="product.php?id=<?php echo $p['id']; ?>" class="overlay-btn" title="Quick View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <!-- Wishlist toggle acts to remove item from wishlist page -->
                                        <button class="overlay-btn wishlist-btn wishlisted" data-product-id="<?php echo $p['id']; ?>" title="Remove from Wishlist">
                                            <i class="fas fa-heart text-danger"></i>
                                        </button>
                                        <button class="overlay-btn btn-add-cart" data-product-id="<?php echo $p['id']; ?>" title="Add to Cart" <?php echo ($p['stock'] <= 0) ? 'disabled' : ''; ?>>
                                            <i class="fas fa-shopping-cart"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="card-body p-4 d-flex flex-column">
                                    <span class="text-muted small mb-1"><?php echo htmlspecialchars($p['category_name'] ?? 'Uncategorized'); ?></span>
                                    <h6 class="fw-bold mb-2">
                                        <a href="product.php?id=<?php echo $p['id']; ?>" class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars($p['name']); ?>
                                        </a>
                                    </h6>
                                    <!-- Rating -->
                                    <div class="rating mb-3">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fa<?php echo $i <= floor($p['rating']) ? 's' : 'r'; ?> fa-star text-warning" style="font-size:0.8rem;"></i>
                                        <?php endfor; ?>
                                        <span class="small text-muted ms-1">(<?php echo $p['rating']; ?>)</span>
                                    </div>
                                    <!-- Price & CTA -->
                                    <div class="mt-auto d-flex align-items-center justify-content-between">
                                        <div class="price">
                                            <?php if ($has_sale): ?>
                                                <span class="text-decoration-line-through text-muted small me-1"><?php echo format_price($p['price']); ?></span>
                                                <span class="fw-bold text-primary" style="font-size:1.15rem;"><?php echo format_price($p['sale_price']); ?></span>
                                            <?php else: ?>
                                                <span class="fw-bold text-primary" style="font-size:1.15rem;"><?php echo format_price($p['price']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <button class="btn btn-primary btn-sm btn-add-cart" data-product-id="<?php echo $p['id']; ?>" style="border-radius:50px; padding:6px 16px;" <?php echo ($p['stock'] <= 0) ? 'disabled' : ''; ?>>
                                            <i class="fas fa-shopping-cart me-1"></i> Add
                                        </button>
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

<!-- Load Wishlist script dependencies -->
<script src="js/cart.js"></script>

<?php require_once 'includes/footer.php'; ?>
