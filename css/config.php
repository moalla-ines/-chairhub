<?php
// Start session with secure settings for Comfort Chairs
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_lifetime' => 86400, // 1 day
        'cookie_secure' => isset($_SERVER['HTTPS']), // Only send over HTTPS
        'cookie_httponly' => true, // Prevent JavaScript access
        'cookie_samesite' => 'Strict' // Prevent CSRF
    ]);
}

// Database configuration for Comfort Chairs
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'e_commerce'); // Your database name

// Create connection
try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper functions for Comfort Chairs
function redirect($url) {
    header("Location: $url");
    exit();
}

function is_logged_in() {
    return isset($_SESSION['iduser']); // Matches your user ID session variable
}

function is_admin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

function base_url() {
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
}

function app_path() {
    $script_path = dirname($_SERVER['SCRIPT_NAME']);
    return rtrim($script_path, '/'); // Adjusted for your e-commerce structure
}

function asset_url($path) {
    return base_url() . app_path() . '/assets/' . ltrim($path, '/');
}

function public_url($path) {
    return base_url() . app_path() . '/' . ltrim($path, '/');
}

function admin_url($path) {
    return base_url() . app_path() . '/admin/' . ltrim($path, '/');
}

function auth_url($path) {
    return base_url() . app_path() . '/auth/' . ltrim($path, '/');
}

// E-commerce specific functions
function get_cart_count() {
    if (!empty($_SESSION['cart'])) {
        return array_reduce($_SESSION['cart'], function($carry, $item) {
            return $carry + $item['quantity'];
        }, 0);
    }
    return 0;
}

function format_price($amount) {
    return '$' . number_format($amount, 2);
}

function get_product($product_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    return $stmt->fetch();
}
?>