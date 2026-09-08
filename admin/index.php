<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

require_once '../db.php';

// Admin check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Get stats
$product_count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$active_products = $pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
$order_count = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pending_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$user_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

$pageTitle = "Admin Dashboard - Taan Tech";
include '../header.php';
?>

<div class="container">
    <h1 class="section-title">Admin Dashboard</h1>

    <div class="admin-stats">
        <div class="stat-card">
            <h3>Products</h3>
            <p><?php echo $product_count; ?> total</p>
            <p><?php echo $active_products; ?> active</p>
        </div>
        <div class="stat-card">
            <h3>Orders</h3>
            <p><?php echo $order_count; ?> total</p>
            <p><?php echo $pending_orders; ?> pending</p>
        </div>
        <div class="stat-card">
            <h3>Users</h3>
            <p><?php echo $user_count; ?> registered</p>
        </div>
    </div>

    <div class="admin-menu">
        <a href="products.php" class="btn btn-primary">Manage Products</a>
        <a href="orders.php" class="btn btn-primary">View Orders</a>
    </div>
</div>

<?php include '../footer.php'; ?>