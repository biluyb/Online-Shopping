<?php
/**
 * ShopVerse Admin Categories Manager
 * Handles CRUD operations for categories.
 */

$page_title = 'Categories';
require_once __DIR__ . '/admin_header.php';

$action = $_GET['action'] ?? 'list';
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle Delete
if ($action === 'delete' && $category_id > 0) {
    // Check if category has products
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $stmt->execute([$category_id]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['flash_message'] = "Cannot delete category because it contains products.";
            $_SESSION['flash_type'] = "error";
        } else {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$category_id]);
            $_SESSION['flash_message'] = "Category deleted successfully.";
            $_SESSION['flash_type'] = "success";
        }
    } catch (PDOException $e) {
        $_SESSION['flash_message'] = "Failed to delete category.";
        $_SESSION['flash_type'] = "error";
    }
    redirect('categories.php');
    exit;
}

// Handle Add / Edit Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($action, ['add', 'edit'])) {
    $name = sanitize_input($_POST['name'] ?? '');
    $slug = sanitize_input($_POST['slug'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    
    // Auto-generate slug if empty
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
    }

    $errors = [];
    if (empty($name)) $errors[] = "Category name is required.";

    // Handle Image Upload
    $image_path = $_POST['current_image'] ?? '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../uploads/categories/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($ext, $allowed)) {
            $new_filename = uniqid('cat_') . '.' . $ext;
            $destination = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                $image_path = 'uploads/categories/' . $new_filename;
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
                $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description, image) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $slug, $description, $image_path]);
                $_SESSION['flash_message'] = "Category added successfully.";
            } else {
                $stmt = $pdo->prepare("UPDATE categories SET name=?, slug=?, description=?, image=? WHERE id=?");
                $stmt->execute([$name, $slug, $description, $image_path, $category_id]);
                $_SESSION['flash_message'] = "Category updated successfully.";
            }
            $_SESSION['flash_type'] = "success";
            redirect('categories.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>

<div class="page-header">
    <div>
        <h1><?php echo $action === 'add' ? 'Add Category' : ($action === 'edit' ? 'Edit Category' : 'Categories'); ?></h1>
        <div class="breadcrumb">
            <a href="index.php">Admin</a> / 
            <?php if (in_array($action, ['add', 'edit'])): ?>
                <a href="categories.php">Categories</a> / <?php echo ucfirst($action); ?>
            <?php else: ?>
                Categories
            <?php endif; ?>
        </div>
    </div>
    <?php if ($action === 'list'): ?>
    <div class="header-actions">
        <a href="?action=add" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Category
        </a>
    </div>
    <?php else: ?>
    <div class="header-actions">
        <a href="categories.php" class="btn" style="background: #2d3748; color: #e4e6eb;">
            <i class="fas fa-arrow-left"></i> Back to Categories
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
    // Fetch category data if edit
    $category = ['name' => '', 'slug' => '', 'description' => '', 'image' => ''];
    if ($action === 'edit' && $category_id > 0) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
            $stmt->execute([$category_id]);
            $fetched = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($fetched) $category = $fetched;
        } catch (PDOException $e) {}
    }
    
    // Repopulate from POST if errors occurred
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $category = array_merge($category, $_POST);
    }
    ?>
    <div class="admin-card">
        <form method="POST" action="?action=<?php echo $action; ?><?php echo $category_id ? "&id={$category_id}" : ""; ?>" enctype="multipart/form-data" class="admin-form" style="max-width: 600px;">
            <div class="form-group">
                <label for="name">Category Name *</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="slug">Slug (URL friendly)</label>
                <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars($category['slug']); ?>" placeholder="Leave blank to auto-generate">
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($category['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label>Category Image</label>
                <?php if (!empty($category['image'])): ?>
                    <div style="margin-bottom: 10px;">
                        <img src="../<?php echo htmlspecialchars($category['image']); ?>" alt="Current Image" style="max-width: 150px; border-radius: 8px; border: 1px solid #2d3748;">
                    </div>
                <?php endif; ?>
                <input type="file" name="image" accept="image/*" style="padding: 6px;">
                <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($category['image']); ?>">
            </div>
            
            <div class="form-actions border-top pt-4" style="border-top: 1px solid #2d3748; margin-top: 20px; padding-top: 20px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> <?php echo $action === 'add' ? 'Save Category' : 'Update Category'; ?>
                </button>
                <a href="categories.php" class="btn" style="background: transparent; color: #9ca3af; border: 1px solid #4b5563;">Cancel</a>
            </div>
        </form>
    </div>

<?php else: ?>
    <?php
    // Fetch categories list with product counts
    try {
        $stmt = $pdo->query("
            SELECT c.*, COUNT(p.id) as product_count
            FROM categories c
            LEFT JOIN products p ON c.id = p.category_id
            GROUP BY c.id
            ORDER BY c.name ASC
        ");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $categories = [];
    }
    ?>

    <div class="admin-card">
        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th width="80">Image</th>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Products</th>
                        <th width="120" style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($categories) > 0): ?>
                        <?php foreach ($categories as $c): ?>
                            <tr>
                                <td>
                                    <?php if ($c['image']): ?>
                                        <img src="../<?php echo htmlspecialchars($c['image']); ?>" alt="Category" class="product-thumb">
                                    <?php else: ?>
                                        <div class="product-thumb" style="display: flex; align-items: center; justify-content: center; background: #2d3748; color: #6b7280;">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo htmlspecialchars($c['name']); ?></strong></td>
                                <td style="color: #9ca3af;"><?php echo htmlspecialchars($c['slug']); ?></td>
                                <td>
                                    <span class="badge" style="background: #2d3748; padding: 4px 10px; border-radius: 12px; font-size: 0.8rem;">
                                        <?php echo $c['product_count']; ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <div style="display: flex; gap: 6px; justify-content: center;">
                                        <a href="?action=edit&id=<?php echo $c['id']; ?>" class="btn btn-icon btn-primary" style="background: rgba(129,140,248,0.15); color: #818cf8;" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($c['product_count'] == 0): ?>
                                        <a href="?action=delete&id=<?php echo $c['id']; ?>" class="btn btn-icon btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this category?');">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                        <?php else: ?>
                                        <button class="btn btn-icon btn-danger" title="Cannot delete category with products" style="opacity: 0.5; cursor: not-allowed;" disabled>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">
                                <div class="empty-state">
                                    <i class="fas fa-tags"></i>
                                    <p>No categories found.</p>
                                    <a href="?action=add" class="btn btn-primary" style="margin-top: 12px;">Add Your First Category</a>
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
