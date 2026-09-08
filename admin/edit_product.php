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

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: products.php");
    exit;
}

$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die("Invalid CSRF token.");
    }

    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $category = trim($_POST['category']);

    if ($name === '' || $description === '' || $price <= 0 || $category === '') {
        $error = "All fields are required.";
    } else {
        $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category = ? WHERE id = ?");
        $stmt->execute([$name, $description, $price, $stock, $category, $product_id]);

        header("Location: products.php");
        exit;
    }
}

$pageTitle = "Edit Product - Admin";
include '../header.php';
?>

<div class="container">
    <h1 class="section-title">Edit Product</h1>

    <?php if ($error): ?>
        <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <label>Product Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>

        <label>Description</label>
        <textarea name="description" required><?php echo htmlspecialchars($product['description']); ?></textarea>

        <label>Price</label>
        <input type="number" name="price" step="0.01" value="<?php echo $product['price']; ?>" required>

        <label>Stock</label>
        <input type="number" name="stock" min="0" value="<?php echo $product['stock']; ?>" required>

        <label>Category</label>
        <input type="text" name="category" value="<?php echo htmlspecialchars($product['category']); ?>" required>

        <button type="submit" class="btn btn-primary">Update Product</button>
    </form>
</div>

<?php include '../footer.php'; ?>