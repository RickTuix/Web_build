<?php
// Start session if not already started
function ensure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        session_start();
    }
}

// Check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Get current user ID
function get_user_id() {
    return $_SESSION['user_id'] ?? null;
}

// Generate CSRF token
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verify_csrf_token($token) {
    return hash_equals($_SESSION['csrf_token'] ?? '', $token ?? '');
}

// Redirect helper
function redirect($url) {
    header("Location: " . $url);
    exit;
}

// Escape output
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Get cart count for logged-in user
function get_cart_count($pdo) {
    if (!is_logged_in()) return 0;
    $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE user_id = ?");
    $stmt->execute([get_user_id()]);
    return (int)$stmt->fetchColumn();
}

// Format price
function format_price($price) {
    return '$' . number_format($price, 2);
}

// Check if product is in stock
function has_stock($stock) {
    return $stock > 0;
}

// Get user's orders count
function get_orders_count($pdo) {
    if (!is_logged_in()) return 0;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
    $stmt->execute([get_user_id()]);
    return (int)$stmt->fetchColumn();
}
?>