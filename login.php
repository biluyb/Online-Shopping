<?php
/**
 * Online shopping registration system - User Login Page
 */

require_once 'includes/functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    if (is_admin()) {
        redirect('admin/index.php');
    } else {
        redirect('dashboard.php');
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF verification
    $csrf = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!verify_csrf($csrf)) {
        flash('login', 'Security token expired. Please try again.', 'danger');
        redirect('login.php');
    }

    $identity = sanitize_input($_POST['identity'] ?? ''); // username or email
    $password = $_POST['password'] ?? '';

    if (empty($identity) || empty($password)) {
        flash('login', 'Please fill in all credentials.', 'danger');
        redirect('login.php');
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$identity, $identity]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        $user = null;
    }

    if ($user && password_verify($password, $user['password'])) {
        // Check Account Status
        if ($user['status'] === 'inactive') {
            flash('login', 'Your account has been deactivated. Please contact support.', 'danger');
            redirect('login.php');
        }

        // Set Session Keys
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = !empty($user['full_name']) ? $user['full_name'] : $user['username'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_avatar'] = !empty($user['avatar']) ? $user['avatar'] : 'default.png';
        
        // This is exactly what admin/admin_header.php expects on line 26
        $_SESSION['user'] = $user;

        // Redirect appropriately
        flash('global', '👋 Welcome back, ' . htmlspecialchars($_SESSION['user_name']) . '!', 'success');
        
        if (isset($_SESSION['redirect_after_login'])) {
            $dest = $_SESSION['redirect_after_login'];
            unset($_SESSION['redirect_after_login']);
            redirect($dest);
        } else {
            if ($user['role'] === 'admin') {
                redirect('admin/index.php');
            } else {
                redirect('dashboard.php');
            }
        }
    } else {
        flash('login', 'Invalid username/email or password credentials.', 'danger');
        redirect('login.php');
    }
}

$page_title = "Log In Account";
require_once 'includes/header.php';
?>

<!-- ══════════════════════════════════════════════════════
     Login Page Layout
     ══════════════════════════════════════════════════════ -->
<div class="container py-5 d-flex align-items-center justify-content-center" style="min-height: calc(100vh - 200px);">
    <div class="w-100" style="max-width: 450px;">
        
        <!-- Alerts -->
        <?php display_flash('login'); ?>

        <div class="card border-0 shadow-sm p-4 p-md-5" style="border-radius:24px; background:var(--bg-secondary); border: 1px solid var(--border-color);">
            <div class="text-center mb-4">
                <div class="mb-3 d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 50%; background: rgba(var(--color-primary-rgb), 0.1); color: var(--color-primary); font-size: 1.5rem;">
                    <i class="fas fa-sign-in-alt"></i>
                </div>
                <h3 class="fw-bold mb-1">Welcome Back</h3>
                <p class="text-secondary small">Log in to manage your orders and profile settings</p>
            </div>

            <form action="login.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf(); ?>">
                
                <div class="form-group mb-3">
                    <label for="identity">Username or Email</label>
                    <input type="text" class="form-control" id="identity" name="identity" required placeholder="john_doe or john@gmail.com" autocomplete="username">
                </div>

                <div class="form-group mb-4">
                    <label for="password">Account Password</label>
                    <input type="password" class="form-control" id="password" name="password" required placeholder="••••••••" autocomplete="current-password">
                </div>

                <button type="submit" class="btn btn-primary w-100 py-3 mb-3" style="border-radius:50px;">
                    <i class="fas fa-sign-in-alt me-1"></i> Authenticate Account
                </button>
            </form>

            <div class="text-center mt-3">
                <p class="text-secondary small mb-0">Don't have an account yet? 
                    <a href="register.php" class="fw-bold text-decoration-none" style="color:var(--color-primary);">Register here</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
