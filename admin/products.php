<?php
/**
 * Online shopping registration system Admin Products Manager
 * Handles CRUD operations for products.
 */

$page_title = 'Products';
require_once __DIR__ . '/admin_header.php';

$action = $_GET['action'] ?? 'list';
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle Delete
if ($action === 'delete' && $product_id > 0) {
    // Basic delete logic (you might want to handle soft deletes or related constraints)
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $_SESSION['flash_message'] = "Product deleted successfully.";
        $_SESSION['flash_type'] = "success";
    } catch (PDOException $e) {
        $_SESSION['flash_message'] = "Failed to delete product. It might be linked to existing orders.";
        $_SESSION['flash_type'] = "error";
    }
    redirect('products.php');
    exit;
}

// Handle Add / Edit Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['add', 'edit'])) {
    $name = sanitize_input($_POST['name'] ?? '');
    $slug = sanitize_input($_POST['slug'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $compare_price = (float)($_POST['compare_price'] ?? 0);
    $category_id = (int)($_POST['category_id'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    
    // Auto-generate slug if empty
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
    }

    $errors = [];
    if (empty($name)) $errors[] = "Product name is required.";
    if ($price <= 0) $errors[] = "Price must be greater than zero.";
    if ($category_id <= 0) $errors[] = "Please select a category.";

    // Handle Image Upload
    $image_path = $_POST['current_image'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../uploads/products/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid('prod_') . '.' . $ext;
            $destination = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                $image_path = 'uploads/products/' . $new_filename;
            } else {
                $errors[] = "Failed to upload image.";
            }
        } else {
            $errors[] = "Invalid image format.";
        }
    }

    if (empty($errors)) {
        try {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO products (name, slug, description, price, compare_price, category_id, stock, image, is_featured, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$name, $slug, $description, $price, $compare_price, $category_id, $stock, $image_path, $is_featured]);
                $_SESSION['flash_message'] = "Product added successfully.";
            } else {
                $stmt = $pdo->prepare("UPDATE products SET name=?, slug=?, description=?, price=?, compare_price=?, category_id=?, stock=?, image=?, is_featured=?, updated_at=NOW() WHERE id=?");
                $stmt->execute([$name, $slug, $description, $price, $compare_price, $category_id, $stock, $image_path, $is_featured, $product_id]);
                $_SESSION['flash_message'] = "Product updated successfully.";
            }
            $_SESSION['flash_type'] = "success";
            redirect('products.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}

// Fetch categories for dropdown
try {
    $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}
?>

<div class="page-header">
    <div>
        <h1><?php echo $action === 'add' ? 'Add Product' : ($action === 'edit' ? 'Edit Product' : 'Products'); ?></h1>
        <div class="breadcrumb">
            <a href="index.php">Admin</a> / 
            <?php if (in_array($action, ['add', 'edit'])): ?>
                <a href="products.php">Products</a> / <?php echo ucfirst($action); ?>
            <?php else: ?>
                Products
            <?php endif; ?>
        </div>
    </div>
    <?php if ($action === 'list'): ?>
    <div class="header-actions">
        <a href="?action=add" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Product
        </a>
    </div>
    <?php else: ?>
    <div class="header-actions">
        <a href="products.php" class="btn" style="background: #2d3748; color: #e4e6eb;">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>
    <?php endif; ?>
</div>

<?php if (!empty($errors)): ?>
    <div class="flash-message error">
        <i class="fas fa-exclamation-circle"></i>
        <div>
            <?php foreach ($errors as $err): ?>
                <div style="margin-bottom: 4px;"><?php echo htmlspecialchars($err); ?></div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php if (in_array($action, ['add', 'edit'])): ?>
    <?php
    // Fetch product data if edit
    $product = [
        'name' => '', 'slug' => '', 'description' => '', 'price' => '', 
        'compare_price' => '', 'category_id' => '', 'stock' => '0', 
        'image' => '', 'is_featured' => 0
    ];
    if ($action === 'edit' && $product_id > 0) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $fetched = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($fetched) $product = $fetched;
        } catch (PDOException $e) {}
    }
    
    // Repopulate from POST if errors occurred
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $product = array_merge($product, $_POST);
        $product['is_featured'] = isset($_POST['is_featured']) ? 1 : 0;
    }
    ?>
    <div class="admin-card">
        <form method="POST" action="?action=<?php echo $action; ?><?php echo $product_id ? "&id={$product_id}" : ""; ?>" enctype="multipart/form-data" class="admin-form" style="max-width: 800px;">
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="name">Product Name *</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="slug">Slug (URL friendly)</label>
                        <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars($product['slug']); ?>" placeholder="Leave blank to auto-generate">
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" rows="5"><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label for="price">Price ($) *</label>
                            <input type="number" step="0.01" min="0" id="price" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="compare_price">Compare at Price ($)</label>
                            <input type="number" step="0.01" min="0" id="compare_price" name="compare_price" value="<?php echo htmlspecialchars($product['compare_price']); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="category_id">Category *</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">-- Select Category --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $product['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="stock">Stock Quantity</label>
                        <input type="number" min="0" id="stock" name="stock" value="<?php echo htmlspecialchars($product['stock']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Product Image</label>
                        <?php if (!empty($product['image'])): ?>
                            <div style="margin-bottom: 10px;">
                                <img src="../<?php echo htmlspecialchars($product['image']); ?>" alt="Current Image" style="max-width: 100%; border-radius: 8px; border: 1px solid #2d3748;">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="image" accept="image/*" style="padding: 6px;">
                        <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($product['image']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-check">
                            <input type="checkbox" name="is_featured" value="1" <?php echo $product['is_featured'] ? 'checked' : ''; ?>>
                            <span>Mark as Featured Product</span>
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="form-actions border-top pt-4" style="border-top: 1px solid #2d3748; margin-top: 20px; padding-top: 20px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?php echo $action === 'add' ? 'Save Product' : 'Update Product'; ?>
                </button>
                <a href="products.php" class="btn" style="background: transparent; color: #9ca3af; border: 1px solid #4b5563;">Cancel</a>
            </div>
        </form>
    </div>

<?php else: ?>
    <?php
    // Fetch products list
    $search = sanitize_input($_GET['search'] ?? '');
    $filter_cat = (int)($_GET['category'] ?? 0);
    
    $where_clauses = [];
    $params = [];
    
    if ($search) {
        $where_clauses[] = "p.name LIKE ?";
        $params[] = "%{$search}%";
    }
    if ($filter_cat > 0) {
        $where_clauses[] = "p.category_id = ?";
        $params[] = $filter_cat;
    }
    
    $where_sql = $where_clauses ? 'WHERE ' . implode(' AND ', $where_clauses) : '';
    
    try {
        $stmt = $pdo->prepare("
            SELECT p.id, p.name, p.price, p.stock, p.image, p.is_featured, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            $where_sql
            ORDER BY p.id DESC
        ");
        $stmt->execute($params);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $products = [];
    }
    ?>

    <div class="admin-card">
        <div class="filter-bar">
            <form method="GET" action="products.php" style="display: flex; gap: 12px; width: 100%; flex-wrap: wrap;">
                <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>" style="flex: 1; min-width: 200px;">
                <select name="category" style="width: auto;">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $filter_cat == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary" style="padding: 8px 16px;">Filter</button>
                <?php if ($search || $filter_cat): ?>
                    <a href="products.php" class="btn" style="background: transparent; color: #9ca3af;">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th width="60">Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Featured</th>
                        <th width="120" style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($products) > 0): ?>
                        <?php foreach ($products as $p): ?>
                            <tr>
                                <td>
                                    <?php if ($p['image']): ?>
                                        <img src="../<?php echo htmlspecialchars($p['image']); ?>" alt="Product" class="product-thumb">
                                    <?php else: ?>
                                        <div class="product-thumb" style="display: flex; align-items: center; justify-content: center; background: #2d3748; color: #6b7280;">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo htmlspecialchars($p['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($p['category_name'] ?? 'Uncategorized'); ?></td>
                                <td>$<?php echo number_format($p['price'], 2); ?></td>
                                <td>
                                    <?php if ($p['stock'] > 10): ?>
                                        <span style="color: #22c55e;"><?php echo $p['stock']; ?></span>
                                    <?php elseif ($p['stock'] > 0): ?>
                                        <span style="color: #eab308;"><?php echo $p['stock']; ?></span>
                                    <?php else: ?>
                                        <span style="color: #ef4444;">Out of stock</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($p['is_featured']): ?>
                                        <i class="fas fa-star" style="color: #eab308;" title="Featured"></i>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <div style="display: flex; gap: 6px; justify-content: center;">
                                        <a href="?action=edit&id=<?php echo $p['id']; ?>" class="btn btn-icon btn-primary" style="background: rgba(129,140,248,0.15); color: #818cf8;" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="?action=delete&id=<?php echo $p['id']; ?>" class="btn btn-icon btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this product?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fas fa-box-open"></i>
                                    <p>No products found.</p>
                                    <a href="?action=add" class="btn btn-primary" style="margin-top: 12px;">Add Your First Product</a>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
