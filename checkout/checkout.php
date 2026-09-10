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

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("
    SELECT c.id AS cart_id, c.quantity, p.id AS product_id, p.name, p.price, p.stock
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

if (count($cart_items) === 0) {
    header("Location: cart.php");
    exit;
}

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die("Invalid CSRF token.");
    }

    $full_name = trim($_POST['full_name']);
    $address = trim($_POST['address']);
    $city = trim($_POST['city']);
    $zip = trim($_POST['zip']);
    $country = trim($_POST['country']);

    if ($full_name === '' || $address === '' || $city === '' || $zip === '' || $country === '') {
        $error = "All fields are required.";
    } else {
        $stock_ok = true;
        foreach ($cart_items as $item) {
            if ($item['quantity'] > $item['stock']) {
                $stock_ok = false;
                $error = "Insufficient stock for " . $item['name'];
                break;
            }
        }

        if ($stock_ok) {
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'completed')");
                $stmt->execute([$user_id, $total]);
                $order_id = $pdo->lastInsertId();

                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_time) VALUES (?, ?, ?, ?)");
                $updateStock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");

                foreach ($cart_items as $item) {
                    $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
                    $updateStock->execute([$item['quantity'], $item['product_id']]);
                }

                $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user_id]);
                $pdo->commit();
                header("Location: order_confirmation.php?order_id=" . $order_id);
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}

$pageTitle = "Checkout - Taan Tech";
include '../header.php';
?>

<div class="container">
    <h1 class="section-title">Checkout</h1>

    <?php if ($error): ?>
        <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <div class="checkout-layout">
        <div class="shipping-form">
            <h2>Shipping Information</h2>
            <form method="POST" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <label>Full Name</label>
                <input type="text" name="full_name" required placeholder="John Doe">
                <label>Address</label>
                <input type="text" name="address" required placeholder="123 Main St">
                <label>City</label>
                <input type="text" name="city" required placeholder="New York">
                <label>ZIP Code</label>
                <input type="text" name="zip" required placeholder="10001">
                <label>Country</label>
                <input type="text" name="country" required placeholder="USA">
                <button type="submit" class="btn btn-primary">Place Order</button>
            </form>
        </div>

        <div class="order-summary">
            <h2>Order Summary</h2>
            <ul>
                <?php foreach ($cart_items as $item): ?>
                    <li>
                        <span><?php echo htmlspecialchars($item['name']); ?> (x<?php echo $item['quantity']; ?>)</span>
                        <span>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="total">
                <strong>Total: $<?php echo number_format($total, 2); ?></strong>
            </div>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>