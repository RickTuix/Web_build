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

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

// Handle status update
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die("Invalid CSRF token.");
    }
    $new_status = $_POST['status'];
    $allowed = ['completed', 'cancelled'];
    if (in_array($new_status, $allowed)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        $message = "Order status updated to " . ucfirst($new_status) . ".";
    }
}

// Fetch order
$stmt = $pdo->prepare("SELECT o.*, u.username FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: orders.php");
    exit;
}

// Fetch order items with product images
$stmt = $pdo->prepare("
    SELECT oi.*, p.name, 
           COALESCE((SELECT url FROM images WHERE product_id = p.id AND is_primary = 1 LIMIT 1), '') AS image_url
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();

$pageTitle = "Order #" . $order_id . " - Admin";
include '../header.php';
?>

<div class="container">
    <h1 class="section-title">Order #<?php echo $order['id']; ?></h1>

    <?php if ($message): ?>
        <p class="success-message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <!-- Order info card -->
    <div class="info-card">
        <div class="info-row">
            <strong>Customer:</strong> <?php echo htmlspecialchars($order['username']); ?>
        </div>
        <div class="info-row">
            <strong>Date:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?>
        </div>
        <div class="info-row">
            <strong>Status:</strong> <?php echo ucfirst($order['status']); ?>
        </div>
        <div class="info-row">
            <strong>Total:</strong> $<?php echo number_format($order['total_amount'], 2); ?>
        </div>
    </div>
    <!-- Shipping Information card -->
<div class="info-card">
    <h2>Shipping Information</h2>
    <div class="info-row">
        <strong>Full Name:</strong> <?php echo htmlspecialchars($order['full_name'] ?? '—'); ?>
    </div>
    <div class="info-row">
        <strong>Address:</strong> <?php echo htmlspecialchars($order['address'] ?? '—'); ?>
    </div>
    <div class="info-row">
        <strong>City:</strong> <?php echo htmlspecialchars($order['city'] ?? '—'); ?>
    </div>
    <div class="info-row">
        <strong>ZIP Code:</strong> <?php echo htmlspecialchars($order['zip'] ?? '—'); ?>
    </div>
    <div class="info-row">
        <strong>Country:</strong> <?php echo htmlspecialchars($order['country'] ?? '—'); ?>
    </div>
</div>

    <!-- Update Status form -->
    <div class="info-card">
        <h2>Update Status</h2>
        <form method="POST" class="admin-form">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <label>Order Status</label>
            <select name="status">
                <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
            <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
        </form>
    </div>

    <!-- Items card -->
    <div class="info-card">
        <h2>Items</h2>
        <ul class="order-items-list">
            <?php foreach ($order_items as $item): ?>
                <li class="order-item">
                    <?php if ($item['image_url']): ?>
                        <img src="<?php echo $base_url . '/' . htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="order-item-img">
                    <?php else: ?>
                        <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4MCIgaGVpZ2h0PSI4MCI+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0iI2VlZSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBhbGlnbm1lbnQtYmFzZWxpbmU9Im1pZGRsZSIgZm9udC1zaXplPSIxMCIgZmlsbD0iIzY2NiIgdGV4dC1hbmNob3I9Im1pZGRsZSI+Tm8gSW1nPC90ZXh0Pjwvc3ZnPg==" alt="No image" class="order-item-img">
                    <?php endif; ?>
                    <div class="order-item-details">
                        <span><?php echo htmlspecialchars($item['name']); ?> (x<?php echo $item['quantity']; ?>)</span>
                        <span class="order-item-price">$<?php echo number_format($item['price_at_time'] * $item['quantity'], 2); ?></span>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
</div>

<?php include '../footer.php'; ?>