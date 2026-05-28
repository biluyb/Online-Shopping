<?php
/**
 * Online shopping registration system - Category Landing Page
 */

require_once 'includes/functions.php';

// Validate Slug
if (!isset($_GET['slug'])) {
    redirect('shop.php');
}

$category_slug = sanitize_input($_GET['slug']);

// Fetch Category Details
try {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ?");
    $stmt->execute([$category_slug]);
    $category = $stmt->fetch();
} catch (PDOException $e) {
    $category = null;
}

if (!$category) {
    $page_title = "Category Not Found";
    require_once 'includes/header.php';
    ?>
    <section class="py-5 text-center">
        <div class="container">
            <i class="fas fa-exclamation-triangle fa-5x text-warning mb-4"></i>
            <h1 class="fw-bold">Category Not Found</h1>
            <p class="text-muted mb-4">Sorry, the category you are looking for does not exist or has been removed.</p>
            <a href="shop.php" class="btn btn-primary" style="border-radius: 50px;">
                <i class="fas fa-arrow-left me-2"></i> Go to Shop
            </a>
        </div>
    </section>
    <?php
    require_once 'includes/footer.php';
    exit;
}

// Fetch Products under this Category
$products = [];
try {
    $stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.category_id = ? ORDER BY p.created_at DESC");
    $stmt->execute([$category['id']]);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
}

$page_title = htmlspecialchars($category['name']) . " Collection";
require_once 'includes/header.php';
?>

<!-- ══════════════════════════════════════════════════════
     Category Header Banner
     ══════════════════════════════════════════════════════ -->
<section class="py-5" style="background: linear-gradient(135deg, rgba(124, 58, 237, 0.05) 0%, rgba(99, 102, 241, 0.05) 100%); border-bottom: 1px solid var(--border-color);">
    <div class="container text-center">
        <div class="mb-3 d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; border-radius: 50%; background: rgba(var(--color-primary-rgb), 0.1); color: var(--color-primary); font-size: 2rem;">
            <i class="<?php echo htmlspecialchars($category['icon']); ?>"></i>
        </div>
        <h1 class="fw-bold mb-2"><?php echo htmlspecialchars($category['name']); ?></h1>
        <p class="text-secondary mb-0">Browse our collection of <?php echo htmlspecialchars($category['name']); ?> products.</p>
        
        <nav aria-label="breadcrumb" class="mt-3">
            <ol class="breadcrumb mb-0 justify-content-center">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="shop.php">Shop</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($category['name']); ?></li>
            </ol>
        </nav>
    </div>
</section>

<!-- ══════════════════════════════════════════════════════
     Category Products Grid
     ══════════════════════════════════════════════════════ -->
<div class="container py-5">
    <?php if (empty($products)): ?>
        <div class="card border-0 shadow-sm text-center py-5" style="border-radius:18px; background:var(--bg-secondary); border:1px solid var(--border-color);">
            <div class="p-5">
                <i class="fas fa-box-open fa-4x text-muted mb-4 opacity-30"></i>
                <h4 class="fw-bold">No Products Available</h4>
                <p class="text-muted">Stay tuned! We are currently restocking products for this category.</p>
                <a href="shop.php" class="btn btn-primary mt-3" style="border-radius:50px;">Browse Shop</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach ($products as $p): ?>
                <?php 
                $has_sale = !empty($p['sale_price']) && $p['sale_price'] > 0;
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
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
