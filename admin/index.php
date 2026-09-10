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

require_once dirname(__DIR__) . '/db.php';

// Admin check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Stats
$product_count = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$active_products = $pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
$order_count = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$completed_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'completed'")->fetchColumn();
$cancelled_orders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'cancelled'")->fetchColumn();
$user_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$pending_carts = $pdo->query("SELECT COUNT(DISTINCT user_id) FROM cart")->fetchColumn();

// Revenue (only completed orders)
$revenue = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = 'completed'")->fetchColumn();

// Top 5 products by quantity sold
$top_products = $pdo->query("
    SELECT p.name, SUM(oi.quantity) AS total_sold, SUM(oi.quantity * oi.price_at_time) AS revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    JOIN orders o ON oi.order_id = o.id
    WHERE o.status = 'completed'
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 5
")->fetchAll();

// Low stock alerts (<= 5)
$low_stock = $pdo->query("SELECT id, name, stock FROM products WHERE stock <= 5 AND is_active = 1 ORDER BY stock ASC")->fetchAll();

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
            <p><?php echo $completed_orders; ?> completed</p>
        </div>
        <div class="stat-card">
            <h3>Cancelled</h3>
            <p><?php echo $cancelled_orders; ?> cancelled</p>
            <p>Refunded / aborted</p>
        </div>
        <div class="stat-card">
            <h3>Users</h3>
            <p><?php echo $user_count; ?> registered</p>
        </div>
        <div class="stat-card">
            <h3>Revenue</h3>
            <p>$<?php echo number_format($revenue, 2); ?></p>
            <p>From completed orders</p>
        </div>
        <div class="stat-card">
            <h3>Pending Carts</h3>
            <p><?php echo $pending_carts; ?> users</p>
            <p>Items not yet checked out</p>
        </div>
    </div>

    <div class="admin-menu">
        <a href="products.php" class="btn btn-primary">Manage Products</a>
        <a href="orders.php" class="btn btn-primary">View Orders</a>
        <a href="carts.php" class="btn btn-primary">Pending Carts</a>
        <a href="users.php" class="btn btn-primary">Manage Users</a>
    </div>

    <!-- Top products -->
    <div class="info-card">
        <h2>Top Selling Products</h2>
        <?php if (count($top_products) > 0): ?>
            <ul class="order-items-list">
                <?php foreach ($top_products as $tp): ?>
                    <li>
                        <span><?php echo htmlspecialchars($tp['name']); ?></span>
                        <span><?php echo $tp['total_sold']; ?> sold — $<?php echo number_format($tp['revenue'], 2); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No completed sales yet.</p>
        <?php endif; ?>
    </div>

    <!-- Low stock alerts -->
    <?php if (count($low_stock) > 0): ?>
        <div class="info-card">
            <h2>⚠️ Low Stock Alerts</h2>
            <ul class="order-items-list">
                <?php foreach ($low_stock as $item): ?>
                    <li>
                        <span><?php echo htmlspecialchars($item['name']); ?></span>
                        <span style="color:#dc3545; font-weight:600;"><?php echo $item['stock']; ?> left</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>

<?php include '../footer.php'; ?>