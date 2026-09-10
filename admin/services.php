<?php
session_set_cookie_params([
    'lifetime' => 0, 'path' => '/', 'domain' => '',
    'secure' => isset($_SERVER['HTTPS']), 'httponly' => true, 'samesite' => 'Lax'
]);
session_start();

require_once dirname(__DIR__) . '/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit;
}

$message = '';
$error = '';

// Add service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_service'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die("Invalid CSRF token.");
    }
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $duration = trim($_POST['duration']);
    $image = trim($_POST['image'] ?? '');

    if ($name === '' || $price < 0) {
        $error = "Please enter a valid name and price.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO services (name, description, image, price, duration) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $image, $price, $duration]);
        $message = "Service added.";
    }
}

// Delist / activate
if (isset($_GET['delist'])) {
    $pdo->prepare("UPDATE services SET is_active = 0 WHERE id = ?")->execute([(int)$_GET['delist']]);
    $message = "Service delisted.";
}
if (isset($_GET['activate'])) {
    $pdo->prepare("UPDATE services SET is_active = 1 WHERE id = ?")->execute([(int)$_GET['activate']]);
    $message = "Service activated.";
}

$services = $pdo->query("SELECT * FROM services ORDER BY id ASC")->fetchAll();

$pageTitle = "Manage Services - Admin";
include '../header.php';
?>

<div class="container">
    <h1 class="section-title">Manage Services</h1>

    <?php if ($message): ?><p class="success-message"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
    <?php if ($error): ?><p class="error-message"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>

    <div class="info-card">
        <h2>Add New Service</h2>
        <form method="POST" class="admin-form">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <label>Service Name</label>
            <input type="text" name="name" required placeholder="e.g., Custom Cable Sleeving">
            <label>Description</label>
            <textarea name="description" placeholder="What does this service include?"></textarea>
            <label>Image Path (optional)</label>
            <input type="text" name="image" placeholder="Images/services/example.jpg">
            <label>Price ($)</label>
            <input type="number" name="price" step="0.01" min="0" required>
            <label>Duration</label>
            <input type="text" name="duration" placeholder="e.g., 1 hour" value="1 hour">
            <button type="submit" name="add_service" class="btn btn-primary">Add Service</button>
        </form>
    </div>

    <div class="admin-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>Name</th><th>Price</th><th>Duration</th><th>Status</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $s): ?>
                    <tr>
                        <td><?php echo $s['id']; ?></td>
                        <td><?php echo htmlspecialchars($s['name']); ?></td>
                        <td>$<?php echo number_format($s['price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($s['duration']); ?></td>
                        <td>
                            <?php if ($s['is_active']): ?>
                                <span class="status-active">Active</span>
                            <?php else: ?>
                                <span class="status-inactive">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($s['is_active']): ?>
                                <a href="services.php?delist=<?php echo $s['id']; ?>" class="btn btn-danger">Delist</a>
                            <?php else: ?>
                                <a href="services.php?activate=<?php echo $s['id']; ?>" class="btn btn-primary">Activate</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="admin-menu" style="margin-top:1.5rem;">
        <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>

<?php include '../footer.php'; ?>