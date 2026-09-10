<?php
// Start session (for cart)
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

// Include database connection
require_once 'db.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch product details (only active products)
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

// If product not found, redirect to listing page
if (!$product) {
    header("Location: products.php");
    exit;
}

// Fetch all images for this product
$imgStmt = $pdo->prepare("SELECT * FROM images WHERE product_id = ? ORDER BY is_primary DESC, id ASC");
$imgStmt->execute([$product_id]);
$images = $imgStmt->fetchAll();

// Set page title
$pageTitle = $product['name'] . " - Taan Tech";
include 'header.php';
?>

<!-- Product Details Section -->
<div class="container">
    <div class="product-details">
        
        <!-- Product Images -->
        <div class="product-gallery">
            <div class="main-image">
                <?php
                if (!empty($images)) {
                    echo '<img src="' . htmlspecialchars($images[0]['url']) . '" alt="' . htmlspecialchars($product['name']) . '" id="mainProductImage">';
                } else {
                    // Placeholder
                    echo '<img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSI2MDAiIGhlaWdodD0iNDAwIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGFsaWdubWVudC1iYXNlbGluZT0ibWlkZGxlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTgiIGZpbGw9IiM2NjYiIHRleHQtYW5jaG9yPSJtaWRkbGUiPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg==" alt="No image available">';
                }
                ?>
            </div>
            <?php if (count($images) > 1): ?>
                <div class="thumbnail-gallery">
                    <?php foreach ($images as $image): ?>
                        <img src="<?php echo htmlspecialchars($image['url']); ?>" 
                             alt="<?php echo htmlspecialchars($image['alt_text'] ?? $product['name']); ?>" 
                             class="thumbnail" 
                             onclick="changeMainImage('<?php echo htmlspecialchars($image['url']); ?>')">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Product Info -->
        <div class="product-info-details">
            <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
            
            <div class="product-meta">
                <span class="product-category">Category: <?php echo htmlspecialchars($product['category']); ?></span>
            </div>

            <p class="product-price">$<?php echo number_format($product['price'], 2); ?></p>
            <p class="product-stock"><?php echo $product['stock'] > 0 ? 'In Stock: ' . $product['stock'] . ' available' : 'Out of Stock'; ?></p>

            <div class="product-description">
                <h3>Description</h3>
                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>

            <!-- Add to Cart Form -->
            <?php if ($product['stock'] > 0): ?>
            <form method="POST" action="<?php echo $base_url; ?>/checkout/add_to_cart.php" class="add-to-cart-form">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            <div class="quantity-selector">
                <label for="quantity">Quantity:</label>
                <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" required>
            </div>
                <button type="submit" class="btn btn-primary">Add to Cart</button>
            </form>
            <?php else: ?>
                <button type="button" class="btn btn-primary" disabled>Out of Stock</button>
            <?php endif; ?>

            <a href="products.php" class="btn btn-secondary back-link">&larr; Back to Products</a>
        </div>

    </div>
</div>

<script>
function changeMainImage(src) {
    document.getElementById('mainProductImage').src = src;
}
</script>

<?php include 'footer.php'; ?>