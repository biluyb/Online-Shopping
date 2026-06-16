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

<!-- Background decoration -->
<div style="position:fixed; inset:0; pointer-events:none; z-index:0;
     background: radial-gradient(ellipse at 20% 50%, rgba(var(--color-primary-rgb),0.06) 0%, transparent 60%),
                 radial-gradient(ellipse at 80% 20%, rgba(var(--color-secondary-rgb),0.05) 0%, transparent 60%);"></div>

<div class="container py-5 d-flex align-items-center justify-content-center"
     style="min-height: calc(100vh - 160px); position:relative; z-index:1;">
    <div class="w-100" style="max-width: 440px;">

        <!-- Flash Alerts -->
        <?php display_flash('login'); ?>

        <!-- Glass Card -->
        <div class="glass-panel p-4 p-md-5">

            <!-- Icon + Heading -->
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center mb-3"
                     style="width:64px; height:64px; border-radius:50%;
                            background:rgba(var(--color-primary-rgb),0.12);
                            color:var(--color-primary); font-size:1.6rem;">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <h2 class="fw-bold mb-1" style="font-size:1.6rem;">Welcome Back</h2>
                <p class="text-secondary small mb-0">Sign in to your account to continue</p>
            </div>

            <form action="login.php" method="POST" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf(); ?>">

                <div class="mb-3">
                    <label for="identity" class="form-label">Username or Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="identity" name="identity"
                               required placeholder="john_doe or john@example.com"
                               autocomplete="username">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password"
                               required placeholder="••••••••"
                               autocomplete="current-password">
                        <button class="input-group-text" type="button" id="togglePassword"
                                style="cursor:pointer; border-left:none;">
                            <i class="fas fa-eye" id="togglePasswordIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-3 mb-3"
                        style="border-radius:var(--radius-full); font-size:1rem;">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </button>
            </form>

            <div class="text-center">
                <p class="text-secondary small mb-0">
                    Don't have an account?
                    <a href="register.php" class="fw-bold" style="color:var(--color-primary);">Create one free</a>
                </p>
            </div>
        </div><!-- /.glass-panel -->

    </div>
</div>

<script>
// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function(){
    const pwd  = document.getElementById('password');
    const icon = document.getElementById('togglePasswordIcon');
    if (pwd.type === 'password') {
        pwd.type  = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        pwd.type  = 'password';
        icon.className = 'fas fa-eye';
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
