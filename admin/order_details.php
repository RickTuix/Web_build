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

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

// Fetch order (any user)
$stmt = $pdo->prepare("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: orders.php");
    exit;
}

// Fetch order items
$stmt = $pdo->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();

$pageTitle = "Order #" . $order_id . " - Admin";
include '../header.php';
?>

<div class="container">
    <h1 class="section-title">Order #<?php echo $order['id']; ?></h1>
    
    <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
    <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
    <p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>
    <p><strong>Total:</strong> $<?php echo number_format($order['total_amount'], 2); ?></p>

    <div class="order-details">
        <h2>Items</h2>
        <ul>
            <?php foreach ($order_items as $item): ?>
                <li>
                    <span><?php echo htmlspecialchars($item['name']); ?> (x<?php echo $item['quantity']; ?>)</span>
                    <span>$<?php echo number_format($item['price_at_time'] * $item['quantity'], 2); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
</div>

<?php include '../footer.php'; ?>