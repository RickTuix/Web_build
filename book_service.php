<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: " . $base_url . "/auth/login.php");
    exit;
}

$service_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND is_active = 1");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    header("Location: " . $base_url . "/index.php#services");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die("Invalid CSRF token.");
    }

    $date = $_POST['booking_date'];
    $time = trim($_POST['booking_time']);
    $notes = trim($_POST['notes'] ?? '');

    if ($date === '' || $time === '') {
        $error = "Please select a date and time.";
    } elseif (strtotime($date) < strtotime(date('Y-m-d'))) {
        $error = "Booking date cannot be in the past.";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO bookings (user_id, service_id, booking_date, booking_time, notes, status)
            VALUES (?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([$_SESSION['user_id'], $service_id, $date, $time, $notes]);
        $success = "Your booking has been submitted! We'll confirm it shortly.";
    }
}

$pageTitle = "Book Service - Taan Tech";
include 'header.php';
?>

<div class="container">
    <h1 class="section-title">Book: <?php echo htmlspecialchars($service['name']); ?></h1>

    <div class="booking-layout">
        <div class="booking-info-card">
            <h2>Service Details</h2>
            <p class="service-card-desc"><?php echo htmlspecialchars($service['description']); ?></p>
            <p><strong>Price:</strong> $<?php echo number_format($service['price'], 2); ?></p>
            <p><strong>Duration:</strong> <?php echo htmlspecialchars($service['duration']); ?></p>
        </div>

        <div class="booking-form-card">
            <h2>Booking Information</h2>

            <?php if ($success): ?>
                <p class="success-message"><?php echo htmlspecialchars($success); ?></p>
                <a href="<?php echo $base_url; ?>/account/account.php" class="btn btn-primary">View My Bookings</a>
            <?php else: ?>
                <?php if ($error): ?>
                    <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>

                <form method="POST" class="auth-form">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <label>Preferred Date</label>
                    <input type="date" name="booking_date" required min="<?php echo date('Y-m-d'); ?>">

                    <label>Preferred Time</label>
                    <select name="booking_time" required>
                        <option value="">-- Select a time --</option>
                        <option value="08:00 AM">08:00 AM</option>
                        <option value="09:00 AM">09:00 AM</option>
                        <option value="10:00 AM">10:00 AM</option>
                        <option value="11:00 AM">11:00 AM</option>
                        <option value="12:00 PM">12:00 PM</option>
                        <option value="01:00 PM">01:00 PM</option>
                        <option value="02:00 PM">02:00 PM</option>
                        <option value="03:00 PM">03:00 PM</option>
                        <option value="04:00 PM">04:00 PM</option>
                        <option value="05:00 PM">05:00 PM</option>
                    </select>

                    <label>Notes (optional)</label>
                    <textarea name="notes" rows="4" placeholder="Any details we should know?"></textarea>

                    <button type="submit" class="btn btn-primary">Confirm Booking</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <a href="<?php echo $base_url; ?>/index.php#services" class="btn btn-secondary" style="margin-top:1.5rem;">&larr; Back to Services</a>
</div>

<?php include 'footer.php'; ?>