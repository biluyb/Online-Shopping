<?php
/**
 * Online Shopping Registration System — Homepage
 */

$page_title = "Home – Fresh Finds & Great Deals";
require_once 'includes/functions.php';
require_once 'includes/header.php';

// Fetch Categories
$categories = [];
$cat_stmt = mysqli_query($conn, "SELECT * FROM categories ORDER BY name ASC");
if ($cat_stmt) {
    while ($row = mysqli_fetch_assoc($cat_stmt)) { $categories[] = $row; }
}

// Fetch Featured Products (Top-rated, max 8)
$featured_products = [];
$prod_stmt = mysqli_query($conn,
    "SELECT p.*, c.name AS category_name
     FROM products p LEFT JOIN categories c ON p.category_id = c.id
     ORDER BY p.rating DESC LIMIT 8");
if ($prod_stmt) {
    while ($row = mysqli_fetch_assoc($prod_stmt)) { $featured_products[] = $row; }
}

// Fetch On-Sale Products (max 4)
$sale_products = [];
$sale_stmt = mysqli_query($conn,
    "SELECT p.*, c.name AS category_name
     FROM products p LEFT JOIN categories c ON p.category_id = c.id
     WHERE p.sale_price > 0 ORDER BY p.created_at DESC LIMIT 4");
if ($sale_stmt) {
    while ($row = mysqli_fetch_assoc($sale_stmt)) { $sale_products[] = $row; }
}
?>

<!-- ══════════════════════════════════════════════════════
     Flash Messages
     ══════════════════════════════════════════════════════ -->
<div class="container pt-4">
    <?php display_flash('login'); ?>
    <?php display_flash('global'); ?>
</div>

<!-- ══════════════════════════════════════════════════════
     HERO SECTION
     ══════════════════════════════════════════════════════ -->
<section class="hero-section py-5" style="
  background: linear-gradient(135deg,
    rgba(var(--color-primary-rgb),0.07) 0%,
    rgba(var(--color-secondary-rgb),0.05) 100%);
  border-bottom: 1px solid var(--border-color);
  min-height: 560px;
  display: flex;
  align-items: center;">
    <div class="container">
        <div class="row align-items-center g-5">

            <!-- Left – Text -->
            <div class="col-lg-6 text-center text-lg-start fade-up">
                <span class="section-badge mb-3 d-inline-block">
                    ✨ Premium Shopping Experience
                </span>
                <h1 class="display-4 fw-bold mb-4" style="letter-spacing:-1.5px; line-height:1.08;">
                    Discover What You<br>
                    <span style="background: linear-gradient(135deg, var(--color-primary), var(--color-secondary));
                                 -webkit-background-clip:text; -webkit-text-fill-color:transparent;">
                        Love to Buy
                    </span>
                </h1>
                <p class="text-secondary mb-5 fs-5" style="line-height:1.7; max-width:480px; margin:0 auto 0;">
                    Explore curated collections, enjoy secure checkout, and get lightning-fast delivery — all in one modern store.
                </p>
                <div class="d-flex flex-column flex-sm-row justify-content-center justify-content-lg-start gap-3">
                    <a href="shop.php" class="btn btn-primary btn-lg" style="border-radius:var(--radius-full); padding:14px 40px;">
                        <i class="fas fa-shopping-bag me-2"></i>Shop Now
                    </a>
                    <a href="#categories" class="btn btn-outline-primary btn-lg" style="border-radius:var(--radius-full); padding:14px 36px;">
                        <i class="fas fa-th-large me-2"></i>Browse Categories
                    </a>
                </div>
            </div>

            <!-- Right – Floating showcase cards -->
            <div class="col-lg-6 d-none d-lg-flex justify-content-center position-relative" style="height:420px;">
                <!-- Card 1 -->
                <div class="card border-0 shadow-lg position-absolute glass-panel"
                     style="width:250px; top:10px; left:30px; border-radius:20px; transform:rotate(-7deg); z-index:2; overflow:hidden;">
                    <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500&auto=format&fit=crop&q=70"
                         alt="Headphones" style="height:170px; object-fit:cover; width:100%;">
                    <div class="p-3">
                        <small class="text-muted" style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.6px;">Electronics</small>
                        <h6 class="fw-bold mb-1" style="font-size:0.88rem;">Wireless Headset Pro</h6>
                        <span class="fw-bold" style="color:var(--color-primary); font-size:0.95rem;">$149.99</span>
                    </div>
                </div>
                <!-- Card 2 -->
                <div class="card border-0 shadow-lg position-absolute glass-panel"
                     style="width:230px; bottom:20px; right:30px; border-radius:20px; transform:rotate(9deg); z-index:1; overflow:hidden;">
                    <img src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500&auto=format&fit=crop&q=70"
                         alt="Watch" style="height:155px; object-fit:cover; width:100%;">
                    <div class="p-3">
                        <small class="text-muted" style="font-size:0.72rem; text-transform:uppercase; letter-spacing:0.6px;">Accessories</small>
                        <h6 class="fw-bold mb-1" style="font-size:0.88rem;">Fitness Tracker X</h6>
                        <span class="fw-bold" style="color:var(--color-primary); font-size:0.95rem;">$129.99</span>
                    </div>
                </div>
                <!-- Badge float -->
                <div class="position-absolute d-flex align-items-center gap-2 glass-panel px-3 py-2"
                     style="top:70px; right:20px; border-radius:var(--radius-full); z-index:5; font-size:0.82rem;">
                    <i class="fas fa-shield-alt" style="color:var(--color-primary);"></i>
                    <span class="fw-600">Secure Checkout</span>
                </div>
            </div>

        </div><!-- /.row -->
    </div><!-- /.container -->
</section>

<!-- ══════════════════════════════════════════════════════
     TRUST BADGES
     ══════════════════════════════════════════════════════ -->
<section class="py-4 bg-section-alt">
    <div class="container">
        <div class="row g-3 row-cols-2 row-cols-md-4">
            <?php
            $badges = [
                ['fas fa-truck-fast',     'Free Shipping',    'On orders over $50'],
                ['fas fa-shield-halved',  'Secure Payment',   '100% protected'],
                ['fas fa-rotate-left',    'Easy Returns',     '30-day policy'],
                ['fas fa-headset',        '24/7 Support',     'We\'re here to help'],
            ];
            foreach ($badges as $b): ?>
            <div class="col">
                <div class="feature-badge">
                    <div class="feature-badge-icon"><i class="<?= $b[0] ?>"></i></div>
                    <div>
                        <h6><?= $b[1] ?></h6>
                        <p><?= $b[2] ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════
     CATEGORIES GRID
     ══════════════════════════════════════════════════════ -->
<section id="categories" class="py-5">
    <div class="container">
        <div class="section-header">
            <span class="section-badge">Explore</span>
            <h2>Shop by Category</h2>
            <p>Browse our wide selection of hand-picked categories</p>
            <div class="section-divider"><span></span><i class="fas fa-star"></i><span></span></div>
        </div>

        <?php if (!empty($categories)): ?>
        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6 g-3 justify-content-center">
            <?php foreach ($categories as $cat): ?>
            <div class="col">
                <a href="category.php?slug=<?= htmlspecialchars($cat['slug']) ?>"
                   class="category-card d-flex flex-column align-items-center">
                    <div class="cat-icon-wrap">
                        <i class="<?= htmlspecialchars($cat['icon']) ?>"></i>
                    </div>
                    <h6><?= htmlspecialchars($cat['name']) ?></h6>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-center text-muted">No categories found.</p>
        <?php endif; ?>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════
     FEATURED PRODUCTS
     ══════════════════════════════════════════════════════ -->
<section class="py-5 bg-section-alt">
    <div class="container">
        <div class="d-flex align-items-end justify-content-between mb-5 flex-wrap gap-3">
            <div>
                <span class="section-badge mb-2 d-inline-block">Top Picks</span>
                <h2 class="mb-1">Featured Products</h2>
                <p class="text-secondary mb-0">High-rated picks recommended by our experts</p>
            </div>
            <a href="shop.php" class="btn btn-outline-primary" style="border-radius:var(--radius-full);">
                View All <i class="fas fa-arrow-right ms-1"></i>
            </a>
        </div>

        <?php if (!empty($featured_products)): ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach ($featured_products as $p):
                $has_sale  = !empty($p['sale_price']) && $p['sale_price'] > 0;
                $image_url = !empty($p['image']) ? htmlspecialchars($p['image']) : 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400&auto=format&fit=crop&q=60';
            ?>
            <div class="col">
                <div class="product-card">
                    <?php if ($has_sale): ?><span class="product-badge">SALE</span><?php endif; ?>
                    <div class="product-image-wrapper">
                        <img src="<?= $image_url ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                        <div class="product-overlay">
                            <a href="product.php?id=<?= $p['id'] ?>" class="overlay-btn" title="Quick View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button class="overlay-btn btn-wishlist" data-product-id="<?= $p['id'] ?>" title="Wishlist">
                                <i class="far fa-heart"></i>
                            </button>
                            <button class="overlay-btn btn-add-cart" data-product-id="<?= $p['id'] ?>"
                                    title="Add to Cart" <?= $p['stock'] <= 0 ? 'disabled' : '' ?>>
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="product-category-label"><?= htmlspecialchars($p['category_name'] ?? 'Uncategorized') ?></p>
                        <h6 class="product-title mb-2">
                            <a href="product.php?id=<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></a>
                        </h6>
                        <div class="star-rating mb-1">
                            <?php for ($i=1;$i<=5;$i++): ?>
                            <i class="fas fa-star<?= $i > $p['rating'] ? ' empty' : '' ?>"></i>
                            <?php endfor; ?>
                            <span class="small text-muted ms-1">(<?= $p['rating'] ?>)</span>
                        </div>
                        <div class="product-price-row">
                            <div>
                                <?php if ($has_sale): ?>
                                <span class="price-original"><?= format_price($p['price']) ?></span>
                                <span class="price-current"><?= format_price($p['sale_price']) ?></span>
                                <?php else: ?>
                                <span class="price-current"><?= format_price($p['price']) ?></span>
                                <?php endif; ?>
                            </div>
                            <button class="btn btn-primary btn-cart-sm btn-add-cart"
                                    data-product-id="<?= $p['id'] ?>"
                                    <?= $p['stock'] <= 0 ? 'disabled' : '' ?>>
                                <i class="fas fa-shopping-cart me-1"></i>Add
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p class="text-center text-muted py-4">No featured products yet.</p>
        <?php endif; ?>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════
     HOT DEALS
     ══════════════════════════════════════════════════════ -->
<?php if (!empty($sale_products)): ?>
<section class="py-5">
    <div class="container">
        <div class="d-flex align-items-end justify-content-between mb-5 flex-wrap gap-3">
            <div>
                <span class="section-badge mb-2 d-inline-block" style="background:rgba(var(--color-secondary-rgb),0.12); color:var(--color-secondary);">
                    🔥 Limited Time
                </span>
                <h2 class="mb-1">Hot Deals</h2>
                <p class="text-secondary mb-0">Massive savings on selected bestsellers</p>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-4">
            <?php foreach ($sale_products as $p):
                $image_url    = !empty($p['image']) ? htmlspecialchars($p['image']) : 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=400&auto=format&fit=crop&q=60';
                $discount_pct = round((($p['price'] - $p['sale_price']) / $p['price']) * 100);
            ?>
            <div class="col">
                <div class="product-card">
                    <span class="product-badge amber">-<?= $discount_pct ?>%</span>
                    <div class="product-image-wrapper">
                        <img src="<?= $image_url ?>" alt="<?= htmlspecialchars($p['name']) ?>">
                        <div class="product-overlay">
                            <a href="product.php?id=<?= $p['id'] ?>" class="overlay-btn" title="Quick View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button class="overlay-btn btn-wishlist" data-product-id="<?= $p['id'] ?>" title="Wishlist">
                                <i class="far fa-heart"></i>
                            </button>
                            <button class="overlay-btn btn-add-cart" data-product-id="<?= $p['id'] ?>"
                                    title="Add to Cart" <?= $p['stock'] <= 0 ? 'disabled' : '' ?>>
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="product-category-label"><?= htmlspecialchars($p['category_name'] ?? 'Uncategorized') ?></p>
                        <h6 class="product-title mb-2">
                            <a href="product.php?id=<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></a>
                        </h6>
                        <div class="product-price-row">
                            <div>
                                <span class="price-original"><?= format_price($p['price']) ?></span>
                                <span class="price-current" style="color:var(--color-secondary);"><?= format_price($p['sale_price']) ?></span>
                            </div>
                            <button class="btn btn-primary btn-cart-sm btn-add-cart"
                                    data-product-id="<?= $p['id'] ?>"
                                    <?= $p['stock'] <= 0 ? 'disabled' : '' ?>>
                                <i class="fas fa-shopping-cart me-1"></i>Add
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════
     CTA BANNER
     ══════════════════════════════════════════════════════ -->
<section class="py-5 bg-section-alt">
    <div class="container">
        <div class="glass-panel text-center p-5" style="
             background: linear-gradient(135deg,
               rgba(var(--color-primary-rgb),0.12) 0%,
               rgba(var(--color-secondary-rgb),0.08) 100%);
             border-color: rgba(var(--color-primary-rgb),0.2);">
            <h2 class="fw-bold mb-3" style="font-size:2rem;">
                Ready to Start Shopping?
            </h2>
            <p class="text-secondary mb-4 fs-5">
                Create a free account today and unlock exclusive member deals.
            </p>
            <div class="d-flex justify-content-center gap-3 flex-wrap">
                <?php if (!is_logged_in()): ?>
                <a href="register.php" class="btn btn-primary btn-lg" style="border-radius:var(--radius-full); padding:13px 38px;">
                    <i class="fas fa-user-plus me-2"></i>Get Started Free
                </a>
                <a href="shop.php" class="btn btn-outline-primary btn-lg" style="border-radius:var(--radius-full); padding:13px 38px;">
                    Browse Products
                </a>
                <?php else: ?>
                <a href="shop.php" class="btn btn-primary btn-lg" style="border-radius:var(--radius-full); padding:13px 38px;">
                    <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
