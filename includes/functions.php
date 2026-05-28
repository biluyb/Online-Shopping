<?php
/**
 * =====================================================
 * Online shopping registration system - Core Helper Functions
 * =====================================================
 * 
 * Centralized utility functions used across the entire
 * application. Handles security, session management,
 * formatting, file uploads, and database helpers.
 * 
 * This file starts the session and includes the DB
 * connection — include it once at the top of every page.
 * =====================================================
 */

// ── Start Session (only if not already active) ────────
if (session_status() === PHP_SESSION_NONE) {
    // Harden session configuration
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

// ── Include Database Connection ───────────────────────
require_once __DIR__ . '/db.php';


// =====================================================
// INPUT SANITIZATION & SECURITY
// =====================================================

/**
 * Sanitize user input to prevent XSS attacks.
 *
 * @param  string $data  Raw user input
 * @return string        Sanitized string
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Generate a CSRF token and store it in the session.
 * Returns the token for embedding in forms.
 *
 * @return string  The generated CSRF token
 */
function generate_csrf() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify a submitted CSRF token against the session token.
 *
 * @param  string $token  The token from the form submission
 * @return bool           True if valid, false otherwise
 */
function verify_csrf($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    $valid = hash_equals($_SESSION['csrf_token'], $token);
    // Regenerate token after verification to prevent reuse
    unset($_SESSION['csrf_token']);
    return $valid;
}


// =====================================================
// NAVIGATION & REDIRECTS
// =====================================================

/**
 * Redirect to a URL and terminate script execution.
 *
 * @param string $url  The URL to redirect to
 */
function redirect($url) {
    header("Location: $url");
    exit();
}


// =====================================================
// AUTHENTICATION HELPERS
// =====================================================

/**
 * Check if the current user is logged in.
 *
 * @return bool  True if a user session exists
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if the current user has admin privileges.
 *
 * @return bool  True if the user role is 'admin'
 */
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}


// =====================================================
// FLASH MESSAGE SYSTEM
// =====================================================

/**
 * Set or retrieve a flash message.
 *
 * To SET a message:   flash('login', 'Login successful!', 'success');
 * To GET a message:   $msg = flash('login');
 *
 * @param  string $key      Unique identifier for the message
 * @param  string $message  The message text (empty to retrieve)
 * @param  string $type     Bootstrap alert type: success, danger, warning, info
 * @return string|void      Returns the message array when getting, void when setting
 */
function flash($key, $message = '', $type = 'success') {
    if (!empty($message)) {
        // SET mode — store the flash message
        $_SESSION['flash'][$key] = [
            'message' => $message,
            'type'    => $type
        ];
    } elseif (isset($_SESSION['flash'][$key])) {
        // GET mode — retrieve and remove the message
        $flash = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $flash;
    }
    return null;
}

/**
 * Render a flash message as styled HTML.
 * Automatically clears the message after display.
 *
 * @param string $key  The flash message key to display
 */
function display_flash($key) {
    $flash = flash($key);
    if ($flash) {
        // Map alert types to Font Awesome icons
        $icons = [
            'success' => 'fas fa-check-circle',
            'danger'  => 'fas fa-exclamation-circle',
            'warning' => 'fas fa-exclamation-triangle',
            'info'    => 'fas fa-info-circle'
        ];
        $icon = $icons[$flash['type']] ?? 'fas fa-info-circle';
        $type = htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8');
        $message = htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8');

        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show d-flex align-items-center" role="alert">';
        echo '  <i class="' . $icon . ' me-2"></i>';
        echo '  <span>' . $message . '</span>';
        echo '  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }
}


// =====================================================
// FORMATTING UTILITIES
// =====================================================

/**
 * Format a numeric price as a currency string.
 *
 * @param  float  $price  The price value
 * @return string         Formatted string, e.g. "$29.99"
 */
function format_price($price) {
    return '$' . number_format((float)$price, 2);
}

/**
 * Generate a URL-friendly slug from a string.
 *
 * @param  string $string  The source string
 * @return string          Lowercase, hyphenated slug
 */
function generate_slug($string) {
    // Convert to lowercase
    $slug = strtolower($string);
    // Replace non-alphanumeric characters with hyphens
    $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
    // Remove consecutive hyphens
    $slug = preg_replace('/-+/', '-', $slug);
    // Trim leading/trailing hyphens
    $slug = trim($slug, '-');
    return $slug;
}

/**
 * Convert a datetime string to a human-readable "time ago" format.
 *
 * @param  string $datetime  A valid datetime string (e.g. '2026-05-20 14:30:00')
 * @return string            Human-readable time difference (e.g. '2 hours ago')
 */
function time_ago($datetime) {
    $now  = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);

    if ($diff->y > 0) {
        return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    }
    if ($diff->m > 0) {
        return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    }
    if ($diff->d > 0) {
        if ($diff->d >= 7) {
            $weeks = floor($diff->d / 7);
            return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
        }
        return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    }
    if ($diff->h > 0) {
        return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    }
    if ($diff->i > 0) {
        return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    }
    return 'Just now';
}


// =====================================================
// DATABASE HELPER FUNCTIONS
// =====================================================

/**
 * Get the number of items in a user's cart.
 *
 * @param  mysqli $conn     Database connection
 * @param  int    $user_id  The user's ID
 * @return int              Total number of cart items
 */
function get_cart_count($conn, $user_id) {
    $stmt = mysqli_prepare($conn, "SELECT COALESCE(SUM(quantity), 0) AS total FROM cart WHERE user_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return (int) $row['total'];
}

/**
 * Get the count of unread notifications for a user.
 *
 * @param  mysqli $conn     Database connection
 * @param  int    $user_id  The user's ID
 * @return int              Number of unread notifications
 */
function get_unread_notifications($conn, $user_id) {
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM notifications WHERE user_id = ? AND is_read = 0");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return (int) $row['total'];
}

/**
 * Retrieve a site setting value by its key.
 *
 * @param  mysqli      $conn  Database connection
 * @param  string      $key   The setting key (e.g. 'site_name')
 * @return string|null        The setting value, or null if not found
 */
function get_setting($conn, $key) {
    $stmt = mysqli_prepare($conn, "SELECT setting_value FROM settings WHERE setting_key = ?");
    mysqli_stmt_bind_param($stmt, "s", $key);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    return $row ? $row['setting_value'] : null;
}


// =====================================================
// FILE UPLOAD HANDLER
// =====================================================

/**
 * Handle image file upload with validation.
 *
 * Validates file type, size, and moves it to the target
 * directory with a unique filename to prevent collisions.
 *
 * @param  array       $file       The $_FILES entry (e.g. $_FILES['image'])
 * @param  string      $directory  Target directory relative to project root (e.g. 'uploads/products')
 * @return string|false            The new filename on success, false on failure
 */
function upload_image($file, $directory = 'uploads') {
    // ── Allowed MIME types and extensions ──────────────
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $allowed_exts  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $max_size      = 5 * 1024 * 1024; // 5 MB

    // ── Validate upload exists and has no errors ──────
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        flash('upload_error', 'File upload failed. Please try again.', 'danger');
        return false;
    }

    // ── Validate file size ────────────────────────────
    if ($file['size'] > $max_size) {
        flash('upload_error', 'File is too large. Maximum size is 5MB.', 'danger');
        return false;
    }

    // ── Validate MIME type ────────────────────────────
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($file['tmp_name']);
    if (!in_array($mime_type, $allowed_types)) {
        flash('upload_error', 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.', 'danger');
        return false;
    }

    // ── Validate extension ────────────────────────────
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_exts)) {
        flash('upload_error', 'Invalid file extension.', 'danger');
        return false;
    }

    // ── Ensure target directory exists ────────────────
    $target_dir = rtrim($directory, '/') . '/';
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    // ── Generate unique filename ──────────────────────
    $new_filename = uniqid('img_', true) . '.' . $ext;
    $target_path  = $target_dir . $new_filename;

    // ── Move the uploaded file ────────────────────────
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return $new_filename;
    }

    flash('upload_error', 'Failed to save uploaded file.', 'danger');
    return false;
}

// ── Compatibility Aliases for Pre-existing files ──────
if (!function_exists('sanitize_input')) {
    /**
     * Alias for sanitize() to support pre-existing files.
     */
    function sanitize_input($data) {
        return sanitize($data);
    }
}

if (!function_exists('generate_csrf_token')) {
    /**
     * Alias for generate_csrf() to support pre-existing files.
     */
    function generate_csrf_token() {
        return generate_csrf();
    }
}

