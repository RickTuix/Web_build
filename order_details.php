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

// Fetch order (only if it belongs to the logged user)
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: account.php");
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

$pageTitle = "Order #" . $order_id . " - Taan Tech";
include 'header.php';
?>

<div class="container">
    <h1 class="section-title">Order #<?php echo $order['id']; ?></h1>

    <!-- Order info card -->
    <div class="info-card">
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

    <!-- Items card -->
    <div class="info-card">
        <h2>Items</h2>
        <ul class="order-items-list">
            <?php foreach ($order_items as $item): ?>
                <li class="order-item">
                    <?php if ($item['image_url']): ?>
                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="order-item-img">
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

    <a href="account.php" class="btn btn-secondary">Back to Account</a>
</div>

<?php include 'footer.php'; ?>