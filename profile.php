<?php
/**
 * Online shopping registration system - User Profile Management Settings
 */

require_once 'includes/auth_check.php';

$user_id = $_SESSION['user_id'];

// Retrieve Current User Row
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
} catch (PDOException $e) {
    die("Database Error: User details could not be retrieved.");
}

// ── Handle Profile Updates (Form 1) ─────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    // CSRF verification
    $csrf = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!verify_csrf($csrf)) {
        flash('profile', 'Security token expired. Please try again.', 'danger');
        redirect('profile.php');
    }

    $fullname = sanitize_input($_POST['fullname'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');

    if (empty($fullname) || empty($email)) {
        flash('profile', 'Name and Email are required fields.', 'danger');
        redirect('profile.php');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('profile', 'Please provide a valid email address.', 'danger');
        redirect('profile.php');
    }

    try {
        // Check email duplicate excluding current user
        $check_stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check_stmt->execute([$email, $user_id]);
        if ($check_stmt->fetch()) {
            flash('profile', 'This email is already in use by another user.', 'danger');
            redirect('profile.php');
        }

        // Handle Avatar File Upload
        $avatar_filename = $user['avatar'];
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploaded = upload_image($_FILES['avatar'], 'uploads/avatars');
            if ($uploaded !== false) {
                // Delete old avatar if it wasn't default
                if ($user['avatar'] !== 'default.png' && file_exists('uploads/avatars/' . $user['avatar'])) {
                    @unlink('uploads/avatars/' . $user['avatar']);
                }
                $avatar_filename = $uploaded;
            } else {
                // upload_image already set flash error
                redirect('profile.php');
            }
        }

        // Update database
        $update_stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, avatar = ? WHERE id = ?");
        $update_stmt->execute([$fullname, $email, $avatar_filename, $user_id]);

        // Sync session variables
        $_SESSION['user_name'] = $fullname;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_avatar'] = $avatar_filename;
        
        // Refresh session user row
        $stmt->execute([$user_id]);
        $_SESSION['user'] = $stmt->fetch();

        flash('profile', '🎉 Profile details successfully updated!', 'success');
        redirect('profile.php');
    } catch (PDOException $e) {
        flash('profile', 'Failed to update details: ' . htmlspecialchars($e->getMessage()), 'danger');
        redirect('profile.php');
    }
}

// ── Handle Password Updates (Form 2) ──────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_password') {
    // CSRF verification
    $csrf = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!verify_csrf($csrf)) {
        flash('profile', 'Security token expired. Please try again.', 'danger');
        redirect('profile.php');
    }

    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        flash('profile', 'Please fill in all password fields.', 'danger');
        redirect('profile.php');
    }

    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        flash('profile', 'Your current password is incorrect.', 'danger');
        redirect('profile.php');
    }

    if (strlen($new_password) < 8) {
        flash('profile', 'New password must be at least 8 characters.', 'danger');
        redirect('profile.php');
    }

    if ($new_password !== $confirm_password) {
        flash('profile', 'Confirm password does not match new password.', 'danger');
        redirect('profile.php');
    }

    try {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update_stmt->execute([$hashed_password, $user_id]);

        // Send Notification of Password Change
        $notif_stmt = $pdo->prepare("INSERT INTO notifications (user_id, icon, message) VALUES (?, 'fa-key', '🔒 Your account password was recently changed. If you did not request this, please contact support.')");
        $notif_stmt->execute([$user_id]);

        flash('profile', '🎉 Password updated successfully!', 'success');
        redirect('profile.php');
    } catch (PDOException $e) {
        flash('profile', 'Failed to update password: ' . htmlspecialchars($e->getMessage()), 'danger');
        redirect('profile.php');
    }
}

// Count Unread notifications for navbar
try {
    $notif_stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND is_read = 0");
    $notif_stmt->execute([$user_id]);
    $unread_notifs = (int)$notif_stmt->fetch()['total'];
} catch (PDOException $e) {
    $unread_notifs = 0;
}
?>

<!-- Custom Style Link -->
<link rel="stylesheet" href="css/dashboard.css">

<!-- ══════════════════════════════════════════════════════
     Breadcrumbs
     ══════════════════════════════════════════════════════ -->
<div class="py-4 mb-4" style="background: linear-gradient(135deg, rgba(124, 58, 237, 0.03) 0%, rgba(99, 102, 241, 0.03) 100%); border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <h1 class="fw-bold mb-2">My Profile Settings</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Settings</li>
            </ol>
        </nav>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     Dashboard Split Layout
     ══════════════════════════════════════════════════════ -->
<div class="container py-4">
    <!-- Alerts -->
    <?php display_flash('profile'); ?>

    <div class="dashboard-grid">
        <!-- Sidebar Navigation Column -->
        <aside class="d-flex flex-column gap-4">
            <div class="user-profile-card">
                <div class="avatar-wrapper mb-3">
                    <img src="uploads/avatars/<?php echo htmlspecialchars($_SESSION['user_avatar'] ?? 'default.png'); ?>" alt="Avatar" class="avatar-img">
                </div>
                <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($_SESSION['user_name']); ?></h5>
                <p class="text-secondary small mb-0"><?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
                <span class="badge bg-secondary-subtle text-secondary mt-2 small" style="border-radius:50px; font-weight:600;">Standard Profile</span>
            </div>

            <div class="menu-list-group">
                <a href="dashboard.php" class="menu-list-item">
                    <i class="fas fa-tachometer-alt"></i> Dashboard Overview
                </a>
                <a href="profile.php" class="menu-list-item active">
                    <i class="fas fa-user-cog"></i> Profile Settings
                </a>
                <a href="orders.php" class="menu-list-item">
                    <i class="fas fa-box"></i> My Orders
                </a>
                <a href="wishlist.php" class="menu-list-item">
                    <i class="fas fa-heart"></i> Favorited Wishlist
                </a>
                <a href="notifications.php" class="menu-list-item d-flex align-items-center justify-content-between">
                    <span><i class="fas fa-bell me-2"></i> Notifications</span>
                    <?php if ($unread_notifs > 0): ?>
                        <span class="badge bg-danger rounded-pill"><?php echo $unread_notifs; ?></span>
                    <?php endif; ?>
                </a>
                <a href="logout.php" class="menu-list-item text-danger">
                    <i class="fas fa-sign-out-alt"></i> Sign Out
                </a>
            </div>
        </aside>

        <!-- Profile Settings Main Column -->
        <main class="d-flex flex-column gap-4">
            <!-- Section 1: Update Details -->
            <div class="sv-form-card">
                <h5 class="fw-bold mb-4"><i class="fas fa-user text-primary me-2"></i>Personal Details</h5>
                
                <form id="profile-form" action="profile.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf(); ?>">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <!-- Avatar Upload -->
                    <div class="mb-4 d-flex align-items-center gap-4">
                        <div class="avatar-preview" data-avatar-preview style="position:relative; width:90px; height:90px;">
                            <img src="uploads/avatars/<?php echo htmlspecialchars($_SESSION['user_avatar'] ?? 'default.png'); ?>" alt="Preview Avatar" class="rounded-circle border" style="width:90px; height:90px; object-fit:cover;">
                        </div>
                        <div>
                            <label class="form-label fw-bold mb-2">Upload Profile Photo</label>
                            <input class="form-control" type="file" id="avatar-upload" data-avatar-upload name="avatar" accept="image/*" style="font-size:0.85rem;">
                            <small class="text-muted d-block mt-1">Accepts JPG, PNG, GIF, WebP (Max: 5MB)</small>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6 form-group">
                            <label for="fullname">Full Name</label>
                            <input type="text" class="form-control" id="fullname" name="fullname" required value="<?php echo htmlspecialchars($user['full_name']); ?>">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="email">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($user['email']); ?>">
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary" style="border-radius:50px;"><i class="fas fa-check-circle me-1"></i> Save Information</button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Section 2: Reset Password -->
            <div class="sv-form-card">
                <h5 class="fw-bold mb-4"><i class="fas fa-key text-primary me-2"></i>Change Security Password</h5>
                
                <form id="password-form" action="profile.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf(); ?>">
                    <input type="hidden" name="action" value="update_password">

                    <div class="row g-3">
                        <div class="col-md-4 form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required placeholder="••••••••">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="new_password">New Password (Min 8 chars)</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required placeholder="••••••••">
                        </div>
                        <div class="col-md-4 form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="••••••••">
                        </div>
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary" style="border-radius:50px;"><i class="fas fa-lock me-1"></i> Update Password</button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
