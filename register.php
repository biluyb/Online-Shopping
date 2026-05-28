<?php
/**
 * Online shopping registration system - User Registration Page
 */

require_once 'includes/functions.php';
require_once 'includes/lang.php';

// Fetch cities for dropdown
$city_stmt = $pdo->query("SELECT id, name FROM cities ORDER BY name ASC");
$cities = $city_stmt->fetchAll(PDO::FETCH_ASSOC);

// Redirect if already logged in
if (is_logged_in()) {
    redirect('dashboard.php');
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF verification
    $csrf = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!verify_csrf($csrf)) {
        flash('register', 'Security token expired. Please try again.', 'danger');
        redirect('register.php');
    }

    $fullname = sanitize_input($_POST['fullname'] ?? '');
    $username = sanitize_input($_POST['username'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $city_id = sanitize_input($_POST['city_id'] ?? '');

    // Validation
    if (empty($fullname) || empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        flash('register', 'Please fill in all registration fields.', 'danger');
        redirect('register.php');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('register', 'Please provide a valid email address.', 'danger');
        redirect('register.php');
    }

    if (strlen($username) < 4) {
        flash('register', 'Username must be at least 4 characters long.', 'danger');
        redirect('register.php');
    }

    if (strlen($password) < 8) {
        flash('register', 'Password must be at least 8 characters long.', 'danger');
        redirect('register.php');
    }

    if ($password !== $confirm_password) {
        flash('register', 'Passwords do not match. Please verify.', 'danger');
        redirect('register.php');
    }

    try {
        // Check for duplicates
        $check_stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check_stmt->execute([$username, $email]);
        if ($check_stmt->fetch()) {
            flash('register', 'Username or Email is already registered.', 'danger');
            redirect('register.php');
        }

        // Encrypt Password and Insert User
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $insert_stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role, status) VALUES (?, ?, ?, ?, 'user', 'active')");
        $insert_stmt->execute([$username, $email, $hashed_password, $fullname]);

        // Send a welcoming notification for new user
        $new_user_id = $pdo->lastInsertId();
        $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, icon, message) VALUES (?, 'fa-user-plus', '🎉 Welcome to Online shopping registration system! Your account has been successfully created. Explore our catalog now.')");
        $notif_stmt->execute([$new_user_id]);

        flash('login', '🎉 Account successfully registered! Please log in below.', 'success');
        redirect('login.php');
    } catch (PDOException $e) {
        flash('register', 'Registration failed. Database error: ' . htmlspecialchars($e->getMessage()), 'danger');
        redirect('register.php');
    }
}

$page_title = "Register Account";
loadLanguage(isset($_GET['lang']) ? $_GET['lang'] : 'en');
require_once 'includes/header.php';
?>

<!-- ══════════════════════════════════════════════════════
     Registration Page Layout
     ══════════════════════════════════════════════════════ -->
<div class="container py-5 d-flex align-items-center justify-content-center" style="min-height: calc(100vh - 200px);">
    <div class="w-100" style="max-width: 500px;">
        
        <!-- Alerts -->
        <?php display_flash('register'); ?>

        <div class="card border-0 shadow-sm p-4 p-md-5" style="border-radius:24px; background:var(--bg-secondary); border: 1px solid var(--border-color);">
            <div class="text-center mb-4">
                <div class="mb-3 d-inline-flex align-items-center justify-content-center" style="width: 60px; height: 60px; border-radius: 50%; background: rgba(var(--color-primary-rgb), 0.1); color: var(--color-primary); font-size: 1.5rem;">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h3 class="fw-bold mb-1"><?php echo t('create_account'); ?></h3>
                <p class="text-secondary small"><?php echo t('register_subtitle'); ?></p>
            </div>

            <form action="register.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf(); ?>">
                

                <div class="form-group mb-3">
                    <label for="username"><?php echo t('username'); ?></label>
                    <input type="text" class="form-control" id="username" name="username" required placeholder="<?php echo t('username_placeholder'); ?>" autocomplete="username">
                </div>

                <div class="form-group mb-3">
                    <label for="email"><?php echo t('email_address'); ?></label>
                    <input type="email" class="form-control" id="email" name="email" required placeholder="<?php echo t('email_placeholder'); ?>" autocomplete="email">
                </div>

                <div class="form-group mb-3">
                    <label for="password"><?php echo t('password'); ?></label>
                    <input type="password" class="form-control" id="password" name="password" required placeholder="<?php echo t('password_placeholder'); ?>" autocomplete="new-password">
                </div>

                <div class="form-group mb-4">
                    <label for="confirm_password"><?php echo t('confirm_password'); ?></label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="<?php echo t('confirm_password_placeholder'); ?>" autocomplete="new-password">
                </div>

                <button type="submit" class="btn btn-primary w-100 py-3 mb-3" style="border-radius:50px;">
                    <i class="fas fa-user-plus me-1"></i> <?php echo t('register_button'); ?>
                </button>
            </form>

            <div class="text-center mt-3">
                <p class="text-secondary small mb-0"><?php echo t('already_have_account'); ?>
                    <a href="login.php" class="fw-bold text-decoration-none" style="color:var(--color-primary);"><?php echo t('login_here'); ?></a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
