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
    $image_url = trim($_POST['image_url']);

    if ($name === '' || $description === '' || $price <= 0 || $category === '') {
        $error = "All fields are required.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, stock, category, is_active) VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->execute([$name, $description, $price, $stock, $category]);
        $product_id = $pdo->lastInsertId();

        // Add image if URL provided
        if ($image_url !== '') {
            $stmt = $pdo->prepare("INSERT INTO images (product_id, url, is_primary) VALUES (?, ?, 1)");
            $stmt->execute([$product_id, $image_url]);
        }

        header("Location: products.php");
        exit;
    }
}

$pageTitle = "Add Product - Admin";
include '../header.php';
?>

<div class="container">
    <h1 class="section-title">Add New Product</h1>

    <?php if ($error): ?>
        <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST" class="admin-form">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <label>Product Name</label>
        <input type="text" name="name" required placeholder="Product name">

        <label>Description</label>
        <textarea name="description" required placeholder="Product description"></textarea>

        <label>Price</label>
        <input type="number" name="price" step="0.01" required placeholder="0.00">

        <label>Stock</label>
        <input type="number" name="stock" min="0" required placeholder="0">

        <label>Category</label>
        <input type="text" name="category" required placeholder="e.g., Laptops, Phones">

        <label>Image URL (optional)</label>
        <input type="url" name="image_url" placeholder="https://example.com/image.jpg">

        <button type="submit" class="btn btn-primary">Add Product</button>
    </form>
</div>

<?php include '../footer.php'; ?>