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

require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

// Fetch order details (only if it belongs to the logged user)
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: index.php");
    exit;
}

// Fetch order items
$stmt = $pdo->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();

$pageTitle = "Order Confirmation - Taan Tech";
include 'header.php';
?>

<div class="container">
    <div class="confirmation-box">
        <h1>Thank you for your order!</h1>
        <p>Your order #<?php echo $order['id']; ?> has been placed successfully.</p>
        <p>Total: $<?php echo number_format($order['total_amount'], 2); ?></p>
    </div>

    <div class="order-details">
        <h2>Order Details</h2>
        <ul>
            <?php foreach ($order_items as $item): ?>
                <li>
                    <span><?php echo htmlspecialchars($item['name']); ?> (x<?php echo $item['quantity']; ?>)</span>
                    <span>$<?php echo number_format($item['price_at_time'] * $item['quantity'], 2); ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <a href="products.php" class="btn btn-primary">Continue Shopping</a>
</div>

<?php include 'footer.php'; ?>