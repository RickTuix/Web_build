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

$message = '';
$error = '';

// Handle promote / demote
if (isset($_GET['promote'])) {
    $user_id = (int)$_GET['promote'];
    if ($user_id === $_SESSION['user_id']) {
        $error = "You cannot change your own role.";
    } else {
        $stmt = $pdo->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
        $stmt->execute([$user_id]);
        $message = "User promoted to admin.";
    }
}

if (isset($_GET['demote'])) {
    $user_id = (int)$_GET['demote'];
    if ($user_id === $_SESSION['user_id']) {
        $error = "You cannot change your own role.";
    } else {
        $stmt = $pdo->prepare("UPDATE users SET role = 'user' WHERE id = ?");
        $stmt->execute([$user_id]);
        $message = "User demoted to customer.";
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $user_id = (int)$_GET['delete'];
    if ($user_id === $_SESSION['user_id']) {
        $error = "You cannot delete your own account.";
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $message = "User deleted.";
    }
}

// Fetch all users with order counts
$stmt = $pdo->query("
    SELECT u.*, 
           (SELECT COUNT(*) FROM orders WHERE user_id = u.id) AS order_count
    FROM users u 
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();

$pageTitle = "Manage Users - Admin";
include '../header.php';
?>

<div class="container">
    <h1 class="section-title">Manage Users</h1>

    <?php if ($message): ?>
        <p class="success-message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <div class="admin-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Orders</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone'] ?? '—'); ?></td>
                        <td>
                            <?php if ($user['role'] === 'admin'): ?>
                                <span class="status-active">Admin</span>
                            <?php else: ?>
                                Customer
                            <?php endif; ?>
                        </td>
                        <td><?php echo $user['order_count']; ?></td>
                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <?php if ($user['role'] === 'user'): ?>
                                <a href="users.php?promote=<?php echo $user['id']; ?>" class="btn btn-primary">Promote</a>
                            <?php else: ?>
                                <a href="users.php?demote=<?php echo $user['id']; ?>" class="btn btn-secondary">Demote</a>
                            <?php endif; ?>
                            <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                <a href="users.php?delete=<?php echo $user['id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this user? This cannot be undone.');">Delete</a>
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