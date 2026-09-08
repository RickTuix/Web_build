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

$user_id = $_SESSION['user_id'];

// Handle remove from cart (if clicked)
if (isset($_GET['remove'])) {
    $cart_id = (int)$_GET['remove'];
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
    header("Location: cart.php");
    exit;
}

// Fetch cart items with product details
$stmt = $pdo->prepare("
    SELECT c.id AS cart_id, c.quantity, p.id AS product_id, p.name, p.price, p.stock, 
           COALESCE((SELECT url FROM images WHERE product_id = p.id AND is_primary = 1 LIMIT 1), '') AS image_url
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

// Calculate total
$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

$pageTitle = "Your Cart - Taan Tech";
include 'header.php';
?>

<div class="container">
    <h1 class="section-title">Your Cart</h1>

    <?php if (isset($_SESSION['cart_message'])): ?>
        <p class="success-message"><?php echo htmlspecialchars($_SESSION['cart_message']); ?></p>
        <?php unset($_SESSION['cart_message']); ?>
    <?php endif; ?>

    <?php if (count($cart_items) > 0): ?>
        <div class="cart-items">
            <?php foreach ($cart_items as $item): ?>
                <div class="cart-item">
                    <div class="cart-item-image">
                        <?php if ($item['image_url']): ?>
                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                        <?php else: ?>
                            <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGFsaWdubWVudC1iYXNlbGluZT0ibWlkZGxlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTIiIGZpbGw9IiM2NjYiIHRleHQtYW5jaG9yPSJtaWRkbGUiPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg==" alt="No image">
                        <?php endif; ?>
                    </div>
                    <div class="cart-item-details">
                        <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                        <p>Price: $<?php echo number_format($item['price'], 2); ?></p>
                        <p>Quantity: <?php echo (int)$item['quantity']; ?></p>
                        <p>Subtotal: $<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                        <a href="cart.php?remove=<?php echo $item['cart_id']; ?>" class="btn btn-danger">Remove</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="cart-total">
            <h3>Total: $<?php echo number_format($total, 2); ?></h3>
            <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
        </div>
    <?php else: ?>
    <div class="cart-empty">
        <div class="cart-empty-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#1a73e8" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="9" cy="21" r="1"></circle>
                <circle cx="20" cy="21" r="1"></circle>
                <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
            </svg>
        </div>
        <h2>Your cart is empty</h2>
        <p>Looks like you haven't added anything yet. Let's fix that!</p>
        <a href="products.php" class="btn btn-primary">Continue Shopping</a>
    </div>
<?php endif; ?>
</div>

<?php include 'footer.php'; ?>