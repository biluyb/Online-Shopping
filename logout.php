<?php
/**
 * Online shopping registration system - User Logout Controller
 */

require_once 'includes/functions.php';

// Clear Session Arrays
$_SESSION = [];

// Destroy Cookies if necessary
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy Session
session_destroy();

// Redirect back to Homepage storefront
redirect('index.php');
exit;
?>
