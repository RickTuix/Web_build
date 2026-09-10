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

// Get all cart items grouped by user
$stmt = $pdo->query("
    SELECT c.id AS cart_id, c.quantity, c.added_at,
           u.id AS user_id, u.username, u.email,
           p.id AS product_id, p.name AS product_name, p.price,
           COALESCE((SELECT url FROM images WHERE product_id = p.id AND is_primary = 1 LIMIT 1), '') AS image_url
    FROM cart c
    JOIN users u ON c.user_id = u.id
    JOIN products p ON c.product_id = p.id
    ORDER BY u.username, c.added_at DESC
");
$cart_items = $stmt->fetchAll();

// Group by user
$users_carts = [];
foreach ($cart_items as $item) {
    $users_carts[$item['user_id']]['username'] = $item['username'];
    $users_carts[$item['user_id']]['email'] = $item['email'];
    $users_carts[$item['user_id']]['items'][] = $item;
}

$pageTitle = "Pending Carts - Admin";
include '../header.php';
?>

<div class="container">
    <h1 class="section-title">Pending Carts</h1>
    <p style="color:#ccc; margin-bottom:1.5rem;">Items users have added but not yet checked out.</p>

    <?php if (count($users_carts) > 0): ?>
        <?php foreach ($users_carts as $user_id => $data): ?>
            <div class="info-card">
                <h2><?php echo htmlspecialchars($data['username']); ?> 
                    <span style="font-size:0.8rem; color:#777;">(<?php echo htmlspecialchars($data['email']); ?>)</span>
                </h2>
                <ul class="order-items-list">
                    <?php foreach ($data['items'] as $item): ?>
                        <li class="order-item">
                            <?php if ($item['image_url']): ?>
                                <img src="<?php echo $base_url . '/' . htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="order-item-img">
                            <?php else: ?>
                                <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI4MCIgaGVpZ2h0PSI4MCI+PHJlY3Qgd2lkdGg9IjEwMCUiIGhlaWdodD0iMTAwJSIgZmlsbD0iI2VlZSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBhbGlnbm1lbnQtYmFzZWxpbmU9Im1pZGRsZSIgZm9udC1zaXplPSIxMCIgZmlsbD0iIzY2NiIgdGV4dC1hbmNob3I9Im1pZGRsZSI+Tm8gSW1nPC90ZXh0Pjwvc3ZnPg==" alt="No image" class="order-item-img">
                            <?php endif; ?>
                            <div class="order-item-details">
                                <span><?php echo htmlspecialchars($item['product_name']); ?> (x<?php echo $item['quantity']; ?>)</span>
                                <span class="order-item-price">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="info-card" style="text-align:center; padding:3rem;">
            <h2>No pending carts</h2>
            <p>No users currently have items in their carts.</p>
        </div>
    <?php endif; ?>

    <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
</div>

<?php include '../footer.php'; ?>