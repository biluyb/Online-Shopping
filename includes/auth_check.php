<?php
/**
 * =====================================================
 * Online shopping registration system - Authentication Guard
 * =====================================================
 * 
 * Include this file at the top of any page that requires
 * an authenticated user. Redirects to the login page if
 * no active session is found.
 * 
 * Usage: require_once 'includes/auth_check.php';
 * 
 * Note: This file expects functions.php to be loaded
 * first (for is_logged_in, flash, and redirect helpers).
 * =====================================================
 */

// ── Ensure functions.php is loaded ────────────────────
if (!function_exists('is_logged_in')) {
    require_once __DIR__ . '/functions.php';
}

// ── Guard: Redirect if not authenticated ──────────────
if (!is_logged_in()) {
    // Store the intended destination so we can redirect back after login
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];

    // Set a user-friendly flash message
    flash('login', 'Please log in to access this page.', 'warning');

    // Redirect to the login page
    redirect('login.php');
}
