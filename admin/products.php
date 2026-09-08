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

// Handle delist (set is_active = 0)
if (isset($_GET['delist'])) {
    $product_id = (int)$_GET['delist'];
    $stmt = $pdo->prepare("UPDATE products SET is_active = 0 WHERE id = ?");
    $stmt->execute([$product_id]);
    header("Location: products.php");
    exit;
}

// Handle reactivate (set is_active = 1)
if (isset($_GET['activate'])) {
    $product_id = (int)$_GET['activate'];
    $stmt = $pdo->prepare("UPDATE products SET is_active = 1 WHERE id = ?");
    $stmt->execute([$product_id]);
    header("Location: products.php");
    exit;
}

// Fetch all products
$products = $pdo->query("SELECT * FROM products ORDER BY created_at DESC")->fetchAll();

$pageTitle = "Admin Products - Taan Tech";
include '../header.php';
?>

<div class="container">
    <h1 class="section-title">Manage Products</h1>

    <div class="admin-actions">
        <a href="add_product.php" class="btn btn-primary">Add New Product</a>
    </div>

    <div class="admin-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo $product['id']; ?></td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td>$<?php echo number_format($product['price'], 2); ?></td>
                        <td><?php echo $product['stock']; ?></td>
                        <td><?php echo htmlspecialchars($product['category']); ?></td>
                        <td>
                            <?php if ($product['is_active']): ?>
                                <span class="status-active">Active</span>
                            <?php else: ?>
                                <span class="status-inactive">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary">Edit</a>
                            <?php if ($product['is_active']): ?>
                                <a href="products.php?delist=<?php echo $product['id']; ?>" class="btn btn-danger">Delist</a>
                            <?php else: ?>
                                <a href="products.php?activate=<?php echo $product['id']; ?>" class="btn btn-primary">Activate</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../footer.php'; ?>