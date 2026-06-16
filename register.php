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

<!-- Background decoration -->
<div style="position:fixed; inset:0; pointer-events:none; z-index:0;
     background: radial-gradient(ellipse at 80% 50%, rgba(var(--color-primary-rgb),0.06) 0%, transparent 55%),
                 radial-gradient(ellipse at 20% 80%, rgba(var(--color-secondary-rgb),0.05) 0%, transparent 55%);"></div>

<div class="container py-5 d-flex align-items-center justify-content-center"
     style="min-height: calc(100vh - 160px); position:relative; z-index:1;">
    <div class="w-100" style="max-width: 500px;">

        <!-- Alerts -->
        <?php display_flash('register'); ?>

        <!-- Glass Card -->
        <div class="glass-panel p-4 p-md-5">

            <!-- Heading -->
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center mb-3"
                     style="width:64px; height:64px; border-radius:50%;
                            background:rgba(var(--color-primary-rgb),0.12);
                            color:var(--color-primary); font-size:1.6rem;">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h2 class="fw-bold mb-1" style="font-size:1.6rem;"><?php echo t('create_account'); ?></h2>
                <p class="text-secondary small mb-0"><?php echo t('register_subtitle'); ?></p>
            </div>

            <form action="register.php" method="POST" id="registerForm">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf(); ?>">

                <!-- Full Name -->
                <div class="mb-3">
                    <label for="fullname" class="form-label">Full Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                        <input type="text" class="form-control" id="fullname" name="fullname"
                               required placeholder="John Doe" autocomplete="name">
                    </div>
                </div>

                <!-- Username -->
                <div class="mb-3">
                    <label for="username" class="form-label"><?php echo t('username'); ?></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-at"></i></span>
                        <input type="text" class="form-control" id="username" name="username"
                               required placeholder="<?php echo t('username_placeholder'); ?>"
                               autocomplete="username" minlength="4">
                    </div>
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label"><?php echo t('email_address'); ?></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email"
                               required placeholder="<?php echo t('email_placeholder'); ?>"
                               autocomplete="email">
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label for="password" class="form-label"><?php echo t('password'); ?></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password"
                               required placeholder="Min. 8 characters"
                               autocomplete="new-password" minlength="8">
                        <button class="input-group-text" type="button" id="togglePass1"
                                style="cursor:pointer; border-left:none;">
                            <i class="fas fa-eye" id="togglePass1Icon"></i>
                        </button>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="mb-4">
                    <label for="confirm_password" class="form-label"><?php echo t('confirm_password'); ?></label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-check-double"></i></span>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                               required placeholder="Re-enter your password"
                               autocomplete="new-password">
                        <button class="input-group-text" type="button" id="togglePass2"
                                style="cursor:pointer; border-left:none;">
                            <i class="fas fa-eye" id="togglePass2Icon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-3 mb-3"
                        style="border-radius:var(--radius-full); font-size:1rem;">
                    <i class="fas fa-user-plus me-2"></i><?php echo t('register_button'); ?>
                </button>
            </form>

            <div class="text-center">
                <p class="text-secondary small mb-0">
                    <?php echo t('already_have_account'); ?>
                    <a href="login.php" class="fw-bold" style="color:var(--color-primary);"><?php echo t('login_here'); ?></a>
                </p>
            </div>
        </div><!-- /.glass-panel -->
    </div>
</div>

<script>
function togglePasswordField(btnId, iconId, fieldId) {
    document.getElementById(btnId).addEventListener('click', function(){
        const field = document.getElementById(fieldId);
        const icon  = document.getElementById(iconId);
        if (field.type === 'password') {
            field.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            field.type = 'password';
            icon.className = 'fas fa-eye';
        }
    });
}
togglePasswordField('togglePass1', 'togglePass1Icon', 'password');
togglePasswordField('togglePass2', 'togglePass2Icon', 'confirm_password');
</script>

<?php require_once 'includes/footer.php'; ?>

