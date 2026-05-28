<?php
/**
 * Online shopping registration system Admin Users Manager
 * Manage customers and administrators.
 */

$page_title = 'Users';
require_once __DIR__ . '/admin_header.php';

$action = $_GET['action'] ?? 'list';
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Prevent modifying the main admin account (ID 1)
$is_protected_admin = ($user_id === 1);

// Handle Delete
if ($action === 'delete' && $user_id > 0) {
    if ($is_protected_admin) {
        $_SESSION['flash_message'] = "Cannot delete the primary administrator account.";
        $_SESSION['flash_type'] = "error";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $_SESSION['flash_message'] = "User deleted successfully.";
            $_SESSION['flash_type'] = "success";
        } catch (PDOException $e) {
            $_SESSION['flash_message'] = "Failed to delete user. They may have existing orders.";
            $_SESSION['flash_type'] = "error";
        }
    }
    redirect('users.php');
    exit;
}

// Handle Role Update / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'edit' && $user_id > 0) {
    if ($is_protected_admin && $_POST['role'] !== 'admin') {
        $_SESSION['flash_message'] = "Cannot change the role of the primary administrator.";
        $_SESSION['flash_type'] = "error";
    } else {
        $role = sanitize_input($_POST['role'] ?? 'customer');
        $valid_roles = ['customer', 'admin'];
        
        if (in_array($role, $valid_roles)) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                $stmt->execute([$role, $user_id]);
                $_SESSION['flash_message'] = "User role updated successfully.";
                $_SESSION['flash_type'] = "success";
            } catch (PDOException $e) {
                $_SESSION['flash_message'] = "Failed to update user.";
                $_SESSION['flash_type'] = "error";
            }
        }
    }
    redirect('users.php');
    exit;
}

?>

<div class="page-header">
    <div>
        <h1>Users</h1>
        <div class="breadcrumb">
            <a href="index.php">Admin</a> / Users
        </div>
    </div>
</div>

<?php if ($action === 'edit' && $user_id > 0): ?>
    <?php
    // Fetch user details for editing
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $edit_user = null;
    }
    
    if (!$edit_user):
    ?>
        <div class="flash-message error">User not found.</div>
    <?php else: ?>
        <div class="admin-card" style="max-width: 500px;">
            <div class="card-header">
                <h3>Edit User Role: <?php echo htmlspecialchars($edit_user['full_name']); ?></h3>
            </div>
            <form method="POST" action="?action=edit&id=<?php echo $edit_user['id']; ?>" class="admin-form">
                <div class="form-group mb-3">
                    <label>Email</label>
                    <input type="email" value="<?php echo htmlspecialchars($edit_user['email']); ?>" disabled class="form-control" style="background: rgba(45,55,72,0.3);">
                </div>
                <div class="form-group mb-4">
                    <label>Role</label>
                    <select name="role" class="form-control" <?php echo $is_protected_admin ? 'disabled' : ''; ?>>
                        <option value="customer" <?php echo $edit_user['role'] === 'customer' ? 'selected' : ''; ?>>Customer</option>
                        <option value="admin" <?php echo $edit_user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
                
                <div class="form-actions" style="margin-top: 0;">
                    <button type="submit" class="btn btn-primary" <?php echo $is_protected_admin ? 'disabled' : ''; ?>>Save Changes</button>
                    <a href="users.php" class="btn" style="background: transparent; color: #9ca3af; border: 1px solid #4b5563;">Cancel</a>
                </div>
            </form>
        </div>
    <?php endif; ?>
<?php else: ?>
    <?php
    // Fetch users list
    $filter_role = sanitize_input($_GET['role'] ?? '');
    $search = sanitize_input($_GET['search'] ?? '');
    
    $where_clauses = [];
    $params = [];
    
    if ($filter_role) {
        $where_clauses[] = "role = ?";
        $params[] = $filter_role;
    }
    
    if ($search) {
        $where_clauses[] = "(full_name LIKE ? OR email LIKE ?)";
        $params[] = "%{$search}%";
        $params[] = "%{$search}%";
    }
    
    $where_sql = $where_clauses ? 'WHERE ' . implode(' AND ', $where_clauses) : '';
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users $where_sql ORDER BY created_at DESC");
        $stmt->execute($params);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $users = [];
    }
    ?>

    <div class="admin-card">
        <div class="filter-bar">
            <form method="GET" action="users.php" style="display: flex; gap: 12px; width: 100%; flex-wrap: wrap;">
                <input type="text" name="search" placeholder="Search by name or email..." value="<?php echo htmlspecialchars($search); ?>" style="flex: 1; min-width: 200px;">
                <select name="role" style="width: auto;">
                    <option value="">All Roles</option>
                    <option value="customer" <?php echo $filter_role === 'customer' ? 'selected' : ''; ?>>Customers</option>
                    <option value="admin" <?php echo $filter_role === 'admin' ? 'selected' : ''; ?>>Admins</option>
                </select>
                <button type="submit" class="btn btn-primary" style="padding: 8px 16px;">Filter</button>
                <?php if ($search || $filter_role): ?>
                    <a href="users.php" class="btn" style="background: transparent; color: #9ca3af;">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <div style="overflow-x: auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th width="50">ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Registered</th>
                        <th width="120" style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><?php echo $u['id']; ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <?php if (!empty($u['avatar'])): ?>
                                            <img src="../<?php echo htmlspecialchars($u['avatar']); ?>" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover;">
                                        <?php else: ?>
                                            <div style="width: 30px; height: 30px; border-radius: 50%; background: #2d3748; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: #9ca3af;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        <?php endif; ?>
                                        <strong><?php echo htmlspecialchars($u['full_name']); ?></strong>
                                    </div>
                                </td>
                                <td style="color: #9ca3af;"><?php echo htmlspecialchars($u['email']); ?></td>
                                <td>
                                    <?php if ($u['role'] === 'admin'): ?>
                                        <span class="status-badge shipped" style="font-size: 0.7rem;">Admin</span>
                                    <?php else: ?>
                                        <span class="status-badge" style="background: #2d3748; color: #9ca3af; font-size: 0.7rem;">Customer</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($u['created_at'])); ?></td>
                                <td style="text-align: center;">
                                    <div style="display: flex; gap: 6px; justify-content: center;">
                                        <a href="?action=edit&id=<?php echo $u['id']; ?>" class="btn btn-icon btn-primary" style="background: rgba(129,140,248,0.15); color: #818cf8;" title="Edit Role">
                                            <i class="fas fa-user-edit"></i>
                                        </a>
                                        <?php if ($u['id'] !== 1 && $u['id'] !== $_SESSION['user']['id']): ?>
                                            <a href="?action=delete&id=<?php echo $u['id']; ?>" class="btn btn-icon btn-danger" title="Delete User" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-icon btn-danger" title="Cannot delete yourself or primary admin" style="opacity: 0.3; cursor: not-allowed;" disabled>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <i class="fas fa-users"></i>
                                    <p>No users found matching criteria.</p>
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
