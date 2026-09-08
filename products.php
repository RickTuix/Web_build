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

// Get search and category filters from GET (optional)
$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');

// Build query with prepared statement (safe)
$sql = "SELECT * FROM products WHERE is_active = 1";
$params = [];

if ($search !== '') {
    $sql .= " AND (name LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category !== '') {
    $sql .= " AND category = ?";
    $params[] = $category;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Placeholder categories (can be pulled from DB later)
$categories = ['Laptops', 'Phones', 'Accessories', 'Components'];

// Set page title
$pageTitle = "Shop - Taan Tech";
include 'header.php';
?>

<!-- Breadcrumb / Page header -->
<section class="page-header">
    <div class="container">
        <h1>Buy Stuff</h1>
        <p>Find the latest tech gadgets and parts</p>
    </div>
</section>

<!-- Search and filter section -->
<div class="container">
    <form method="GET" action="products.php" class="search-filter">
        <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
        <select name="category">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($category === $cat) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($cat); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>

    <!-- Product Grid -->
    <?php if (count($products) > 0): ?>
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <!-- Product image -->
                    <div class="product-image">
                        <?php
                        // Fetch primary image for this product
                        $imgStmt = $pdo->prepare("SELECT url FROM images WHERE product_id = ? AND is_primary = 1 LIMIT 1");
                        $imgStmt->execute([$product['id']]);
                        $image = $imgStmt->fetch();

                        if ($image) {
                            echo '<img src="' . htmlspecialchars($image['url']) . '" alt="' . htmlspecialchars($product['name']) . '">';
                        } else {
                            // Placeholder (SVG data URI)
                            echo '<img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMDAiIGhlaWdodD0iMjAwIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGFsaWdubWVudC1iYXNlbGluZT0ibWlkZGxlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM2NjYiIHRleHQtYW5jaG9yPSJtaWRkbGUiPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg==" alt="No image">';
                        }
                        ?>
                    </div>
                    
                    <div class="product-info">
    <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
    <p class="product-price">$<?php echo number_format($product['price'], 2); ?></p>
    <p class="product-stock"><?php echo $product['stock'] > 0 ? 'In Stock (' . $product['stock'] . ')' : 'Out of Stock'; ?></p>
    <p class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 80)) . '...'; ?></p>
</div>

<div class="product-actions">
    <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-secondary">View Details</a>
    <?php if ($product['stock'] > 0): ?>
        <form method="POST" action="add_to_cart.php" class="add-to-cart-form">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
            <input type="hidden" name="quantity" value="1">
            <button type="submit" class="btn btn-primary">Add to Cart</button>
        </form>
    <?php else: ?>
        <button type="button" class="btn btn-primary" disabled>Out of Stock</button>
    <?php endif; ?>
</div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
    <!-- No products found -->
    <div class="no-products">
        <h2>No products found</h2>
        <p>Our inventory is currently being updated. Please check back soon!</p>
    </div>

    <!-- Only show placeholder cards if the shop is completely empty (no search/filter) -->
    <?php if ($search === '' && $category === ''): ?>
        <div class="products-grid">
            <?php for ($i = 0; $i < 3; $i++): ?>
                <div class="product-card placeholder">
                    <div class="product-image">
                        <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMDAiIGhlaWdodD0iMjAwIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGFsaWdubWVudC1iYXNlbGluZT0ibWlkZGxlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTQiIGZpbGw9IiM2NjYiIHRleHQtYW5jaG9yPSJtaWRkbGUiPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg==" alt="Placeholder product">
                    </div>
                    <div class="product-info">
                        <h3 class="product-name">Product Name</h3>
                        <p class="product-price">$99.99</p>
                        <p class="product-description">This is a placeholder description.</p>
                    </div>
                    <div class="product-actions">
                        <button class="btn btn-secondary" disabled>View Details</button>
                        <button class="btn btn-primary" disabled>Add to Cart</button>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php include 'footer.php'; ?>