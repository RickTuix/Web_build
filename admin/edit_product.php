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

// Fetch current image (primary)
$imgStmt = $pdo->prepare("SELECT url FROM images WHERE product_id = ? AND is_primary = 1 LIMIT 1");
$imgStmt->execute([$product_id]);
$current_image = $imgStmt->fetchColumn();

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
    
    $image_url = $current_image; // default to existing image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = dirname(__DIR__) . '/Images/Products/';
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_info = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($file_info, $_FILES['image']['tmp_name']);
        finfo_close($file_info);
        
        if (!in_array($mime, $allowed_types)) {
            $error = "Invalid image type. Only JPG, PNG, GIF, or WebP allowed.";
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $error = "Image too large. Max 5MB.";
        } else {
            $filename = uniqid() . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $target = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $image_url = 'Images/Products/' . $filename;
                // Delete old image file (optional)
                if ($current_image && file_exists(dirname(__DIR__) . '/' . $current_image)) {
                    unlink(dirname(__DIR__) . '/' . $current_image);
                }
            } else {
                $error = "Failed to upload image.";
            }
        }
    }

    if ($name === '' || $description === '' || $price <= 0 || $category === '') {
        $error = "All fields are required.";
    }

    if ($error === '') {
        $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, category = ? WHERE id = ?");
        $stmt->execute([$name, $description, $price, $stock, $category, $product_id]);

        // Update image if changed
        if ($image_url !== $current_image) {
            // Check if product already has an image record
            $check = $pdo->prepare("SELECT id FROM images WHERE product_id = ? AND is_primary = 1");
            $check->execute([$product_id]);
            if ($check->fetch()) {
                $stmt = $pdo->prepare("UPDATE images SET url = ? WHERE product_id = ? AND is_primary = 1");
                $stmt->execute([$image_url, $product_id]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO images (product_id, url, is_primary) VALUES (?, ?, 1)");
                $stmt->execute([$product_id, $image_url]);
            }
        }

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

    <form method="POST" class="admin-form" enctype="multipart/form-data">
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

        <label>Image (optional – leave empty to keep current)</label>
        <?php if ($current_image): ?>
            <p>Current: <img src="/WEBBUILD/<?php echo htmlspecialchars($current_image); ?>" alt="Current image" style="max-height:80px;"></p>
        <?php endif; ?>
        <input type="file" name="image" accept="image/*">

        <button type="submit" class="btn btn-primary">Update Product</button>
    </form>
</div>

<?php include '../footer.php'; ?>