<?php
require_once 'includes/functions.php';

// Get product by ID or slug
$product = null;
if (isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT p.*, c.name AS category_name, c.id AS cat_id FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif (isset($_GET['slug'])) {
    $slug = sanitize_input($_GET['slug']);
    $stmt = $pdo->prepare("SELECT p.*, c.name AS category_name, c.id AS cat_id FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.slug = ?");
    $stmt->execute([$slug]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
}

if (!$product) {
    $page_title = "Product Not Found";
    $page_description = "The requested product could not be found.";
    require_once 'includes/header.php';
    ?>
    <section class="py-5 text-center">
        <div class="container">
            <i class="fas fa-exclamation-triangle fa-5x text-warning mb-4"></i>
            <h1 class="fw-bold">404 - Product Not Found</h1>
            <p class="text-muted mb-4">Sorry, the product you're looking for doesn't exist or has been removed.</p>
            <a href="shop.php" class="btn btn-primary" style="border-radius: 50px; background: #7c3aed; border-color: #7c3aed; padding: 12px 40px;">
                <i class="fas fa-arrow-left me-2"></i> Back to Shop
            </a>
        </div>
    </section>
    <?php
    require_once 'includes/footer.php';
    exit;
}

// Fetch reviews
$stmt = $pdo->prepare("SELECT r.*, u.username, u.full_name, u.avatar FROM reviews r LEFT JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC");
$stmt->execute([$product['id']]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

$review_count = count($reviews);
$avg_rating = $review_count > 0 ? array_sum(array_column($reviews, 'rating')) / $review_count : 0;

// Related products (same category, exclude current)
$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.category_id = ? AND p.id != ? ORDER BY RAND() LIMIT 4");
$stmt->execute([$product['cat_id'], $product['id']]);
$related_products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$has_sale = !empty($product['sale_price']) && $product['sale_price'] > 0;
$discount_pct = $has_sale ? round((($product['price'] - $product['sale_price']) / $product['price']) * 100) : 0;
$image_url = !empty($product['image']) ? htmlspecialchars($product['image']) : 'https://via.placeholder.com/500x500/1a1a2e/7c3aed?text=' . urlencode($product['name']);

$page_title = htmlspecialchars($product['name']);
$page_description = htmlspecialchars($product['short_description'] ?? substr($product['description'] ?? '', 0, 160));
require_once 'includes/header.php';
?>

<!-- Breadcrumbs -->
<nav aria-label="breadcrumb" class="py-3" style="background: #f8f9fa;">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none"><i class="fas fa-home"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="shop.php" class="text-decoration-none">Shop</a></li>
            <?php if (!empty($product['category_name'])): ?>
                <li class="breadcrumb-item"><a href="shop.php?category=<?php echo (int)$product['cat_id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($product['category_name']); ?></a></li>
            <?php endif; ?>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($product['name']); ?></li>
        </ol>
    </div>
</nav>

<!-- Product Detail -->
<section class="product-detail py-5">
    <div class="container">
        <div class="row">
            <!-- Product Image -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
                    <?php if ($has_sale): ?>
                        <span class="badge bg-danger position-absolute" style="top: 15px; left: 15px; z-index: 2; border-radius: 20px; padding: 8px 16px; font-size: 1rem;">
                            -<?php echo $discount_pct; ?>% OFF
                        </span>
                    <?php endif; ?>
                    <img src="<?php echo $image_url; ?>" class="img-fluid" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 100%; height: 500px; object-fit: cover;">
                </div>
            </div>

            <!-- Product Info -->
            <div class="col-md-6">
                <h1 class="fw-bold mb-2"><?php echo htmlspecialchars($product['name']); ?></h1>

                <!-- Rating -->
                <div class="d-flex align-items-center mb-3">
                    <div class="rating me-2">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <?php if ($i <= floor($avg_rating)): ?>
                                <i class="fas fa-star text-warning"></i>
                            <?php elseif ($i - 0.5 <= $avg_rating): ?>
                                <i class="fas fa-star-half-alt text-warning"></i>
                            <?php else: ?>
                                <i class="far fa-star text-warning"></i>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>
                    <span class="text-muted">(<?php echo $review_count; ?> review<?php echo $review_count !== 1 ? 's' : ''; ?>)</span>
                </div>

                <!-- Price -->
                <div class="mb-3">
                    <?php if ($has_sale): ?>
                        <span class="text-decoration-line-through text-muted me-2" style="font-size: 1.5rem;">$<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></span>
                        <span class="fw-bold" style="font-size: 2rem; color: #7c3aed;">$<?php echo htmlspecialchars(number_format($product['sale_price'], 2)); ?></span>
                        <span class="badge bg-danger ms-2" style="border-radius: 20px;">Save <?php echo $discount_pct; ?>%</span>
                    <?php else: ?>
                        <span class="fw-bold" style="font-size: 2rem; color: #7c3aed;">$<?php echo htmlspecialchars(number_format($product['price'], 2)); ?></span>
                    <?php endif; ?>
                </div>

                <!-- Short Description -->
                <?php if (!empty($product['short_description'])): ?>
                    <p class="text-muted mb-4"><?php echo htmlspecialchars($product['short_description']); ?></p>
                <?php endif; ?>

                <!-- Stock Status -->
                <div class="mb-3">
                    <?php if (isset($product['stock']) && $product['stock'] > 0): ?>
                        <span class="badge bg-success" style="border-radius: 20px; padding: 8px 16px;">
                            <i class="fas fa-check-circle me-1"></i> In Stock (<?php echo (int)$product['stock']; ?> available)
                        </span>
                    <?php else: ?>
                        <span class="badge bg-danger" style="border-radius: 20px; padding: 8px 16px;">
                            <i class="fas fa-times-circle me-1"></i> Out of Stock
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Quantity & Add to Cart -->
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="input-group" style="width: 140px;">
                        <button class="btn btn-outline-secondary" type="button" id="qty-minus" aria-label="Decrease quantity">
                            <i class="fas fa-minus"></i>
                        </button>
                        <input type="number" class="form-control text-center" id="product-qty" value="1" min="1" max="<?php echo (int)($product['stock'] ?? 99); ?>" aria-label="Product quantity">
                        <button class="btn btn-outline-secondary" type="button" id="qty-plus" aria-label="Increase quantity">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <button class="btn btn-primary btn-lg btn-add-cart" data-product-id="<?php echo (int)$product['id']; ?>" style="border-radius: 50px; background: #7c3aed; border-color: #7c3aed; padding: 12px 35px;"
                        <?php echo (isset($product['stock']) && $product['stock'] <= 0) ? 'disabled' : ''; ?>>
                        <i class="fas fa-shopping-cart me-2"></i> Add to Cart
                    </button>
                    <button class="btn btn-outline-danger btn-lg rounded-circle btn-wishlist" data-product-id="<?php echo (int)$product['id']; ?>" title="Add to Wishlist" aria-label="Add to wishlist">
                        <i class="fas fa-heart"></i>
                    </button>
                </div>

                <!-- Meta Info -->
                <div class="product-meta text-muted" style="font-size: 0.9rem;">
                    <?php if (!empty($product['sku'])): ?>
                        <p class="mb-1"><strong>SKU:</strong> <?php echo htmlspecialchars($product['sku']); ?></p>
                    <?php endif; ?>
                    <p class="mb-1"><strong>Category:</strong>
                        <a href="shop.php?category=<?php echo (int)$product['cat_id']; ?>" class="text-decoration-none">
                            <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                        </a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Tabs: Description, Reviews, Shipping -->
        <div class="mt-5">
            <ul class="nav nav-tabs" id="productTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="desc-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab" aria-controls="description" aria-selected="true">
                        <i class="fas fa-align-left me-1"></i> Description
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab" aria-controls="reviews" aria-selected="false">
                        <i class="fas fa-star me-1"></i> Reviews (<?php echo $review_count; ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping" type="button" role="tab" aria-controls="shipping" aria-selected="false">
                        <i class="fas fa-truck me-1"></i> Shipping Info
                    </button>
                </li>
            </ul>
            <div class="tab-content border border-top-0 p-4" id="productTabContent" style="border-radius: 0 0 15px 15px;">
                <!-- Description Tab -->
                <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="desc-tab">
                    <?php if (!empty($product['description'])): ?>
                        <div class="product-description">
                            <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No detailed description available for this product.</p>
                    <?php endif; ?>
                </div>

                <!-- Reviews Tab -->
                <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
                    <?php if (!empty($reviews)): ?>
                        <?php foreach ($reviews as $review): ?>
                            <div class="review-item border-bottom pb-3 mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <?php
                                    $reviewer_avatar = !empty($review['avatar']) ? htmlspecialchars($review['avatar']) : 'https://via.placeholder.com/40x40/7c3aed/ffffff?text=' . urlencode(substr($review['full_name'] ?? $review['username'] ?? 'U', 0, 1));
                                    ?>
                                    <img src="<?php echo $reviewer_avatar; ?>" class="rounded-circle me-2" alt="Reviewer" width="40" height="40">
                                    <div>
                                        <strong><?php echo htmlspecialchars($review['full_name'] ?? $review['username'] ?? 'Anonymous'); ?></strong>
                                        <div class="rating">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fa<?php echo $i <= (int)$review['rating'] ? 's' : 'r'; ?> fa-star text-warning" style="font-size: 0.8rem;"></i>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    <small class="text-muted ms-auto"><?php echo htmlspecialchars(date('M d, Y', strtotime($review['created_at']))); ?></small>
                                </div>
                                <?php if (!empty($review['title'])): ?>
                                    <h6 class="fw-bold"><?php echo htmlspecialchars($review['title']); ?></h6>
                                <?php endif; ?>
                                <p class="text-muted mb-0"><?php echo htmlspecialchars($review['comment'] ?? $review['review'] ?? ''); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No reviews yet. Be the first to review this product!</p>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="mt-4">
                            <h5 class="fw-bold">Write a Review</h5>
                            <form method="POST" action="product.php?id=<?php echo (int)$product['id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                                <input type="hidden" name="action" value="submit_review">
                                <div class="mb-3">
                                    <label for="review-rating" class="form-label">Rating</label>
                                    <select class="form-select" id="review-rating" name="rating" required style="width: auto;">
                                        <option value="5">5 - Excellent</option>
                                        <option value="4">4 - Good</option>
                                        <option value="3">3 - Average</option>
                                        <option value="2">2 - Poor</option>
                                        <option value="1">1 - Terrible</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="review-comment" class="form-label">Your Review</label>
                                    <textarea class="form-control" id="review-comment" name="comment" rows="4" required placeholder="Share your experience with this product..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary" style="border-radius: 50px; background: #7c3aed; border-color: #7c3aed;">
                                    <i class="fas fa-paper-plane me-1"></i> Submit Review
                                </button>
                            </form>
                        </div>
                    <?php else: ?>
                        <p class="mt-3"><a href="login.php" class="text-decoration-none" style="color: #7c3aed;">Login</a> to write a review.</p>
                    <?php endif; ?>
                </div>

                <!-- Shipping Tab -->
                <div class="tab-pane fade" id="shipping" role="tabpanel" aria-labelledby="shipping-tab">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold"><i class="fas fa-shipping-fast me-2" style="color: #7c3aed;"></i>Shipping Options</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Free Standard Shipping (5-7 business days) on orders over $50</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Express Shipping (2-3 business days) - $9.99</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Overnight Shipping (1 business day) - $19.99</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold"><i class="fas fa-undo me-2" style="color: #7c3aed;"></i>Return Policy</h6>
                            <ul class="list-unstyled">
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>30-day return policy</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Free returns on all orders</li>
                                <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Full refund or exchange</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($related_products)): ?>
        <section class="related-products mt-5">
            <h3 class="fw-bold mb-4">Related Products</h3>
            <div class="row">
                <?php foreach ($related_products as $rp): ?>
                    <?php
                    $rp_image = !empty($rp['image']) ? htmlspecialchars($rp['image']) : 'https://via.placeholder.com/300x300/1a1a2e/7c3aed?text=' . urlencode($rp['name']);
                    $rp_has_sale = !empty($rp['sale_price']) && $rp['sale_price'] > 0;
                    $rp_rating = isset($rp['rating']) ? (float)$rp['rating'] : 0;
                    ?>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card product-card h-100 border-0 shadow-sm" style="border-radius: 15px; overflow: hidden; transition: transform 0.3s;">
                            <div class="position-relative">
                                <img src="<?php echo $rp_image; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($rp['name']); ?>" style="height: 220px; object-fit: cover;">
                                <div class="product-overlay position-absolute w-100 d-flex justify-content-center gap-2" style="bottom: 10px; opacity: 0; transition: opacity 0.3s;">
                                    <button class="btn btn-sm btn-light rounded-circle" title="Quick View" onclick="window.location.href='product.php?id=<?php echo (int)$rp['id']; ?>'">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-primary rounded-circle btn-add-cart" title="Add to Cart" data-product-id="<?php echo (int)$rp['id']; ?>">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body text-center">
                                <small class="text-muted"><?php echo htmlspecialchars($rp['category_name'] ?? 'Uncategorized'); ?></small>
                                <h6 class="card-title fw-bold mt-1">
                                    <a href="product.php?id=<?php echo (int)$rp['id']; ?>" class="text-decoration-none text-dark">
                                        <?php echo htmlspecialchars($rp['name']); ?>
                                    </a>
                                </h6>
                                <div class="rating mb-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fa<?php echo $i <= floor($rp_rating) ? 's' : 'r'; ?> fa-star text-warning"></i>
                                    <?php endfor; ?>
                                </div>
                                <div class="price">
                                    <?php if ($rp_has_sale): ?>
                                        <span class="text-decoration-line-through text-muted me-2">$<?php echo htmlspecialchars(number_format($rp['price'], 2)); ?></span>
                                        <span class="fw-bold" style="color: #7c3aed;">$<?php echo htmlspecialchars(number_format($rp['sale_price'], 2)); ?></span>
                                    <?php else: ?>
                                        <span class="fw-bold" style="color: #7c3aed;">$<?php echo htmlspecialchars(number_format($rp['price'], 2)); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    </div>
</section>

<style>
.product-card:hover { transform: translateY(-5px); }
.product-card:hover .product-overlay { opacity: 1 !important; }
.nav-tabs .nav-link.active { color: #7c3aed; border-color: #dee2e6 #dee2e6 #fff; }
.nav-tabs .nav-link { color: #6c757d; }
</style>

<script>
document.getElementById('qty-minus')?.addEventListener('click', function() {
    const input = document.getElementById('product-qty');
    if (parseInt(input.value) > 1) input.value = parseInt(input.value) - 1;
});
document.getElementById('qty-plus')?.addEventListener('click', function() {
    const input = document.getElementById('product-qty');
    const max = parseInt(input.max) || 99;
    if (parseInt(input.value) < max) input.value = parseInt(input.value) + 1;
});
</script>

<?php require_once 'includes/footer.php'; ?>
