<?php
// Start session with secure cookie settings FIRST
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

// Include database connection
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// CSRF check
if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
    die("Invalid CSRF token.");
}

$user_id = $_SESSION['user_id'];
$product_id = (int)($_POST['product_id'] ?? 0);
$quantity = max(1, (int)($_POST['quantity'] ?? 1));

// Fetch product stock
$stmt = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    die("Product not found.");
}

if ($quantity > $product['stock']) {
    die("Insufficient stock.");
}

// Insert or update cart
$stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)
                       ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)");
$stmt->execute([$user_id, $product_id, $quantity]);

// Redirect to cart page with success message
$_SESSION['cart_message'] = "Item added to cart successfully!";
header("Location: cart.php");
exit;
?>