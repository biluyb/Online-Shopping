<?php
/**
 * Online shopping registration system - All Categories Grid Page
 */

require_once 'includes/functions.php';

// Fetch Categories and calculate product counts
$categories = [];
try {
    $stmt = $pdo->query("SELECT c.*, COUNT(p.id) AS product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.name ASC");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

$page_title = "Browse Departments";
require_once 'includes/header.php';
?>

<!-- ══════════════════════════════════════════════════════
     Page Breadcrumbs and Header
     ══════════════════════════════════════════════════════ -->
<div class="py-4 mb-4" style="background: linear-gradient(135deg, rgba(124, 58, 237, 0.03) 0%, rgba(99, 102, 241, 0.03) 100%); border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <h1 class="fw-bold mb-2">Departments & Categories</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="shop.php">Shop</a></li>
                <li class="breadcrumb-item active" aria-current="page">Categories</li>
            </ol>
        </nav>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     All Departments Grid
     ══════════════════════════════════════════════════════ -->
<div class="container py-4">
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 justify-content-center">
        <?php foreach ($categories as $cat): ?>
            <div class="col">
                <div class="card h-100 border-0 shadow-sm p-4 text-center d-flex flex-column align-items-center justify-content-between" style="border-radius:20px; background: var(--bg-secondary); border: 1px solid var(--border-color); transition: var(--transition-normal); min-height: 240px;">
                    <div class="mb-3 d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; border-radius: 50%; background: rgba(var(--color-primary-rgb), 0.1); color: var(--color-primary); font-size: 1.8rem;">
                        <i class="<?php echo htmlspecialchars($cat['icon']); ?>"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($cat['name']); ?></h5>
                        <p class="text-muted small mb-0"><?php echo (int)$cat['product_count']; ?> Product<?php echo ($cat['product_count'] !== 1) ? 's' : ''; ?> Available</p>
                    </div>
                    <a href="category.php?slug=<?php echo htmlspecialchars($cat['slug']); ?>" class="btn btn-outline-primary btn-sm mt-3" style="border-radius: 50px;">
                        Explore Collection <i class="fas fa-chevron-right ms-1" style="font-size:0.75rem;"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Custom hover styles -->
<style>
.card:hover {
  transform: translateY(-8px);
  border-color: rgba(var(--color-primary-rgb), 0.3) !important;
  box-shadow: var(--shadow-md) !important;
}
</style>

<?php require_once 'includes/footer.php'; ?>
