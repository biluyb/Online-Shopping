<?php
/**
 * ShopVerse - Interactive Product Catalog Page
 */

require_once 'includes/functions.php';

// Pagination variables
$limit = 8;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Filter and sorting inputs
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$min_price = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float)$_GET['min_price'] : 0.0;
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : 1000.0;
$sort_by = isset($_GET['sort_by']) ? sanitize_input($_GET['sort_by']) : 'default';

// ── Build Dynamic SQL Query ─────────────────────────
$query_parts = [];
$params = [];

$sql = "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";

if ($search !== '') {
    $sql .= " AND (p.name LIKE ? OR p.short_description LIKE ?)";
    $query_parts[] = "%$search%";
    $query_parts[] = "%$search%";
}

if ($category_id > 0) {
    $sql .= " AND p.category_id = ?";
    $query_parts[] = $category_id;
}

if ($min_price > 0) {
    $sql .= " AND COALESCE(NULLIF(p.sale_price, 0), p.price) >= ?";
    $query_parts[] = $min_price;
}

if ($max_price > 0) {
    $sql .= " AND COALESCE(NULLIF(p.sale_price, 0), p.price) <= ?";
    $query_parts[] = $max_price;
}

// Sorting logic
switch ($sort_by) {
    case 'price_asc':
        $sql .= " ORDER BY COALESCE(NULLIF(p.sale_price, 0), p.price) ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY COALESCE(NULLIF(p.sale_price, 0), p.price) DESC";
        break;
    case 'rating':
        $sql .= " ORDER BY p.rating DESC";
        break;
    case 'newest':
        $sql .= " ORDER BY p.created_at DESC";
        break;
    default:
        $sql .= " ORDER BY p.id ASC";
        break;
}

// ── Retrieve Total Count for Pagination ──────────────
try {
    $count_stmt = $pdo->prepare($sql);
    $count_stmt->execute($query_parts);
    $total_products = count($count_stmt->fetchAll());
} catch (PDOException $e) {
    $total_products = 0;
}

$total_pages = ceil($total_products / $limit);

// Append limit/offset
$sql .= " LIMIT ? OFFSET ?";
$query_parts[] = $limit;
$query_parts[] = $offset;

// ── Execute Final Query ──────────────────────────────
$products = [];
try {
    $stmt = $pdo->prepare($sql);
    // Explicitly bind limit and offset as integers for PDO compatibility
    $param_index = 1;
    foreach ($query_parts as $qp) {
        if ($param_index > count($query_parts) - 2) {
            $stmt->bindValue($param_index, $qp, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($param_index, $qp);
        }
        $param_index++;
    }
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $products = [];
}

// Fetch all categories for sidebar filter
$categories = [];
try {
    $cat_query = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $cat_query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}

$page_title = "Shop Products Catalog";
require_once 'includes/header.php';
?>

<!-- ══════════════════════════════════════════════════════
     Storefront Breadcrumbs and Header
     ══════════════════════════════════════════════════════ -->
<div class="py-4 mb-4" style="background: linear-gradient(135deg, rgba(124, 58, 237, 0.03) 0%, rgba(99, 102, 241, 0.03) 100%); border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <h1 class="fw-bold mb-2">Shop Catalog</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item active" aria-current="page">Shop</li>
            </ol>
        </nav>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     Main Grid Catalog Flow
     ══════════════════════════════════════════════════════ -->
<div class="container py-4">
    <div class="row g-4">
        <!-- Sidebar Filters -->
        <aside class="col-lg-3">
            <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius:18px; background: var(--bg-secondary); border: 1px solid var(--border-color);">
                <h5 class="fw-bold mb-4"><i class="fas fa-filter text-primary me-2"></i>Filters</h5>
                
                <form action="shop.php" method="GET">
                    <!-- Search -->
                    <div class="mb-4">
                        <label class="form-label fw-bold small">Search Keywords</label>
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control border-start-0 ps-0" name="search" placeholder="Type here..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>

                    <!-- Category Filter -->
                    <div class="mb-4">
                        <label class="form-label fw-bold small">Product Category</label>
                        <select class="form-select" name="category">
                            <option value="0">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($category_id === (int)$cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Price Limiters -->
                    <div class="mb-4">
                        <label class="form-label fw-bold small">Price Range ($)</label>
                        <div class="d-flex align-items-center gap-2">
                            <input type="number" class="form-control" name="min_price" placeholder="Min" value="<?php echo $min_price > 0 ? htmlspecialchars($min_price) : ''; ?>" min="0">
                            <span class="text-muted">—</span>
                            <input type="number" class="form-control" name="max_price" placeholder="Max" value="<?php echo $max_price < 1000 ? htmlspecialchars($max_price) : ''; ?>" min="0">
                        </div>
                    </div>

                    <!-- Sort -->
                    <div class="mb-4">
                        <label class="form-label fw-bold small">Sort Results By</label>
                        <select class="form-select" name="sort_by">
                            <option value="default" <?php echo ($sort_by === 'default') ? 'selected' : ''; ?>>Default Sorting</option>
                            <option value="price_asc" <?php echo ($sort_by === 'price_asc') ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_desc" <?php echo ($sort_by === 'price_desc') ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="rating" <?php echo ($sort_by === 'rating') ? 'selected' : ''; ?>>Highest Rated</option>
                            <option value="newest" <?php echo ($sort_by === 'newest') ? 'selected' : ''; ?>>Newest Arrivals</option>
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> Apply Filters</button>
                        <a href="shop.php" class="btn btn-outline-secondary"><i class="fas fa-sync-alt me-1"></i> Reset</a>
                    </div>
                </form>
            </div>
        </aside>

        <!-- Product Cards Grid -->
        <main class="col-lg-9">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <span class="text-muted small">Showing <?php echo count($products); ?> of <?php echo $total_products; ?> products</span>
            </div>

            <?php if (empty($products)): ?>
                <div class="card border-0 shadow-sm text-center py-5" style="border-radius:18px; background:var(--bg-secondary); border:1px solid var(--border-color);">
                    <div class="p-5">
                        <i class="fas fa-search fa-4x text-muted mb-4 opacity-30"></i>
                        <h4 class="fw-bold">No Products Found</h4>
                        <p class="text-muted">Try adjusting your keyword searches or category parameters.</p>
                        <a href="shop.php" class="btn btn-primary mt-3" style="border-radius:50px;">Clear Filters</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
                    <?php foreach ($products as $p): ?>
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

                <!-- Catalog Pagination Navigation -->
                <?php if ($total_pages > 1): ?>
                    <nav class="mt-5" aria-label="Page navigation">
                        <ul class="pagination justify-content-center gap-2">
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" style="border-radius:8px;" href="shop.php?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_id; ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price; ?>&sort_by=<?php echo $sort_by; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo ($page === $i) ? 'active' : ''; ?>">
                                    <a class="page-link" style="border-radius:8px;" href="shop.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_id; ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price; ?>&sort_by=<?php echo $sort_by; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" style="border-radius:8px;" href="shop.php?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_id; ?>&min_price=<?php echo $min_price; ?>&max_price=<?php echo $max_price; ?>&sort_by=<?php echo $sort_by; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
