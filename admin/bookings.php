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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die("Invalid CSRF token.");
    }
    $bid = (int)$_POST['booking_id'];
    $status = $_POST['status'];
    if (in_array($status, ['pending', 'confirmed', 'completed', 'cancelled'])) {
        $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?")->execute([$status, $bid]);
        $message = "Booking status updated.";
    }
}

$stmt = $pdo->query("
    SELECT b.*, u.username, u.email, s.name AS service_name
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN services s ON b.service_id = s.id
    ORDER BY b.created_at DESC
");
$bookings = $stmt->fetchAll();

$pageTitle = "Service Bookings - Admin";
include '../header.php';
?>

<div class="container">
    <h1 class="section-title">Service Bookings</h1>

    <?php if ($message): ?><p class="success-message"><?php echo htmlspecialchars($message); ?></p><?php endif; ?>

    <div class="admin-table">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Service</th>
                    <th>Date & Time</th>
                    <th>Notes</th>
                    <th>Status</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($bookings) > 0): ?>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td><?php echo $b['id']; ?></td>
                            <td>
                                <?php echo htmlspecialchars($b['username']); ?><br>
                                <small><?php echo htmlspecialchars($b['email']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($b['service_name']); ?></td>
                            <td>
                                <?php echo date('M j, Y', strtotime($b['booking_date'])); ?><br>
                                <small><?php echo htmlspecialchars($b['booking_time']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($b['notes'] ?? '—'); ?></td>
                            <td><?php echo ucfirst($b['status']); ?></td>
                            <td>
                                <form method="POST" style="display:flex; gap:0.35rem;">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                    <select name="status">
                                        <?php foreach (['pending', 'confirmed', 'completed', 'cancelled'] as $s): ?>
                                            <option value="<?php echo $s; ?>" <?php echo $b['status'] === $s ? 'selected' : ''; ?>>
                                                <?php echo ucfirst($s); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="update_status" class="btn btn-primary">Save</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" style="text-align:center; padding:2rem;">No bookings yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="admin-menu" style="margin-top:1.5rem;">
        <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>
</div>

<?php include '../footer.php'; ?>