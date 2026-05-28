<?php
/**
 * =====================================================
 * Online shopping registration system - Central Database Connection
 * =====================================================
 * 
 * Instantiates both:
 * 1. $conn: MySQLi connection (procedural / standard functions)
 * 2. $pdo: PDO connection (prepared query drivers)
 * 
 * Ensures dual-driver compatibility across all backend scripts.
 * =====================================================
 */

// ── Database Configuration ───────────────────────────
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'fullstack_db');

// ── 1. MySQLi Connection ─────────────────────────────
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("MySQLi Database Connection Failed: " . mysqli_connect_error());
}

// Set character set to support emojis/UTF-8 characters
mysqli_set_charset($conn, "utf8mb4");

// ── 2. PDO Connection ────────────────────────────────
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("PDO Database Connection Failed: " . $e->getMessage());
}
?>
