<?php
/**
 * Online shopping registration system - Storefront Homepage
 */

$page_title = "Home - Premium Shopping Experience";
require_once 'includes/functions.php';
require_once 'includes/header.php';

// Fetch Categories for Grid
$categories = [];
$cat_stmt = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");
if ($cat_stmt) {
    while ($row = mysqli_fetch_assoc($cat_stmt)) {
        $categories[] = $row;
    }
}

// Fetch Featured Products (Max 4)
$featured_products = [];
$prod_stmt = mysqli_query($conn, "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.rating DESC LIMIT 4");
if ($prod_stmt) {
    while ($row = mysqli_fetch_assoc($prod_stmt)) {
        $featured_products[] = $row;
    }
}

// Fetch On-Sale Products (Max 4)
$sale_products = [];
$sale_stmt = mysqli_query($conn, "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.sale_price > 0 ORDER BY p.created_at DESC LIMIT 4");
if ($sale_stmt) {
    while ($row = mysqli_fetch_assoc($sale_stmt)) {
        $sale_products[] = $row;
    }
}
?>

<!-- ══════════════════════════════════════════════════════
     Hero Banner Section (Glassmorphism & Sleek Gradients)
     ══════════════════════════════════════════════════════ -->
<section class="hero-section py-5 d-flex align-items-center" style="background: linear-gradient(135deg, rgba(124, 58, 237, 0.08) 0%, rgba(99, 102, 241, 0.08) 100%); min-height: 550px; border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 text-center text-lg-start">
                <span class="badge bg-primary-subtle text-primary mb-3" style="border-radius: 50px; padding: 8px 20px; font-weight:600; font-size:0.9rem;">
                    🚀 Discover the Online Shopping Registration System
                </span>
                <h1 class="display-3 fw-bold mb-4" style="line-height: 1.1; letter-spacing: -2px; font-family: var(--font-heading);">
                    Elevate Your <br>
                    <span style="background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-secondary) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Online Shopping</span>
                </h1>
                <p class="text-secondary mb-5 fs-5" style="line-height: 1.6;">
                    Explore premium curated catalogs, enjoy seamless transaction security, and experience lightning-fast support. Experience a new benchmark in modern e-commerce.
                </p>
                <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-lg-start gap-3">
                    <a href="shop.php" class="btn btn-primary btn-lg" style="border-radius: 50px; padding: 14px 40px;">
                        <i class="fas fa-shopping-bag me-2"></i> Shop Now
                    </a>
                    <a href="#categories" class="btn btn-outline-primary btn-lg" style="border-radius: 50px; padding: 14px 40px;">
                        <i class="fas fa-th-large me-2"></i> Browse Categories
                    </a>
                </div>
            </div>
<<<<<<< HEAD
=======
            <div class="col-lg-6 d-none d-lg-block text-center position-relative">
                <!-- Visual Floating Card Showcase -->
                <div class="position-relative mx-auto" style="width: 450px; height: 450px;">
                    <div class="card border-0 shadow-lg position-absolute" style="width: 280px; top: 0; left: 0; border-radius: 20px; transform: rotate(-8deg); z-index: 2; overflow: hidden; background: var(--bg-secondary);">
                        <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format&fit=crop&q=60" alt="Showcase 1" class="img-fluid" style="height: 200px; object-fit: cover;">
                        <div class="card-body p-3">
                            <span class="text-muted small">Electronics</span>
>>>>>>> 1472dc0f1291c64f54cc402aaba1498a3f19ad18
                            <h6 class="fw-bold mb-1">Wireless Headset Pro</h6>
                            <span class="fw-bold" style="color: var(--color-primary);">$149.99</span>
                        </div>
                    </div>
                    <div class="card border-0 shadow-lg position-absolute" style="width: 250px; bottom: 10px; right: 0; border-radius: 20px; transform: rotate(10deg); z-index: 1; overflow: hidden; background: var(--bg-secondary);">
                        <img src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&auto=format&fit=crop&q=60" alt="Showcase 2" class="img-fluid" style="height: 180px; object-fit: cover;">
                        <div class="card-body p-3">
                            <span class="text-muted small">Smart Accessories</span>
                            <h6 class="fw-bold mb-1">Fitness Tracker X</h6>
                            <span class="fw-bold" style="color: var(--color-primary);">$129.99</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════
     Flash Message Alert Section
     ══════════════════════════════════════════════════════ -->
<div class="container mt-4">
    <?php display_flash('login'); ?>
    <?php display_flash('global'); ?>
</div>

<!-- ══════════════════════════════════════════════════════
     Browse Categories Grid
     ══════════════════════════════════════════════════════ -->
<section id="categories" class="py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="fw-bold mb-2">Shop by Category</h2>
            <p class="text-secondary">Discover our wide variety of premium products</p>
        </div>
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-4 justify-content-center">
            <?php foreach ($categories as $cat): ?>
                <div class="col">
                    <a href="category.php?slug=<?php echo htmlspecialchars($cat['slug']); ?>" class="card h-100 border-0 shadow-sm text-center p-4 align-items-center justify-content-center" style="border-radius: 18px; background: var(--bg-secondary); border: 1px solid var(--border-color); transition: var(--transition-normal);">
                        <div class="mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 50%; background: rgba(var(--color-primary-rgb), 0.1); color: var(--color-primary); font-size: 1.5rem;">
                            <i class="<?php echo htmlspecialchars($cat['icon']); ?>"></i>
                        </div>
                        <h6 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($cat['name']); ?></h6>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════
     Featured Products Grid
     ══════════════════════════════════════════════════════ -->
<section class="py-5" style="background-color: var(--bg-tertiary);">
    <div class="container">
        <div class="d-flex align-items-end justify-content-between mb-5">
            <div>
                <h2 class="fw-bold mb-2">Featured Products</h2>
                <p class="text-secondary mb-0">High-rated recommendations from our experts</p>
            </div>
            <a href="shop.php" class="btn btn-outline-primary d-none d-sm-inline-block" style="border-radius:50px;">
                View Shop <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
        
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach ($featured_products as $p): ?>
                <?php 
                $has_sale = !empty($p['sale_price']) && $p['sale_price'] > 0;
                $display_price = $has_sale ? $p['sale_price'] : $p['price'];
                $image_url = !empty($p['image']) ? htmlspecialchars($p['image']) : 'https://via.placeholder.com/400x300';
                ?>
                <div class="col">
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
                                <button class="overlay-btn btn-wishlist" data-product-id="<?php echo $p['id']; ?>" title="Add to Wishlist">
                                    <i class="far fa-heart"></i>
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
    </div>
</section>

<!-- ══════════════════════════════════════════════════════
     On-Sale Hot Deals Grid
     ══════════════════════════════════════════════════════ -->
<section class="py-5">
    <div class="container">
        <div class="d-flex align-items-end justify-content-between mb-5">
            <div>
                <h2 class="fw-bold mb-2">🔥 Hot Deals</h2>
                <p class="text-secondary mb-0">Massive savings on selected bestsellers. Act fast!</p>
            </div>
        </div>
        
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach ($sale_products as $p): ?>
                <?php 
                $image_url = !empty($p['image']) ? htmlspecialchars($p['image']) : 'https://via.placeholder.com/400x300';
                $discount_pct = round((($p['price'] - $p['sale_price']) / $p['price']) * 100);
                ?>
                <div class="col">
                    <div class="card h-100 product-card">
                        <span class="product-badge bg-danger">-<?php echo $discount_pct; ?>%</span>
                        
                        <div class="product-image-wrapper">
                            <img src="<?php echo $image_url; ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                            <div class="product-overlay">
                                <a href="product.php?id=<?php echo $p['id']; ?>" class="overlay-btn" title="Quick View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button class="overlay-btn btn-wishlist" data-product-id="<?php echo $p['id']; ?>" title="Add to Wishlist">
                                    <i class="far fa-heart"></i>
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
                                    <span class="text-decoration-line-through text-muted small me-1"><?php echo format_price($p['price']); ?></span>
                                    <span class="fw-bold text-danger" style="font-size:1.15rem;"><?php echo format_price($p['sale_price']); ?></span>
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
    </div>
</section>

<!-- Custom styling for cards on hover in index -->
<style>
.card:hover {
  transform: translateY(-5px);
  border-color: rgba(var(--color-primary-rgb), 0.3) !important;
}
</style>

<?php require_once 'includes/footer.php'; ?>
