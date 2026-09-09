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

$error = '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die("Invalid CSRF token.");
    }

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $error = "Password must be at least 8 characters and include uppercase, lowercase, and a number.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error = "Username or email already taken.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, phone, password) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $email, $phone, $hash]);
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['role'] = 'user';
            header("Location: ../index.php");
            exit;
        }
    }
}

$pageTitle = "Sign Up - Taan Tech";
include '../header.php';
?>

<section class="auth-section">
    <div class="auth-card">
        <h2>Create Account</h2>
        <?php if ($error): ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST" class="auth-form" id="registerForm">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <label>Username</label>
            <input type="text" name="username" required placeholder="Choose a username">
            <label>Email</label>
            <input type="email" name="email" required placeholder="Enter your email">
            <label>Phone (optional)</label>
            <input type="tel" name="phone" placeholder="Enter phone number">
            
            <label>Password</label>
            <input type="password" name="password" id="password" required placeholder="Min. 8 chars, upper, lower, number">
            <div class="password-strength-container">
                <div class="password-strength-bar">
                    <div id="password-strength"></div>
                </div>
                <span class="password-strength-label">Password strength</span>
            </div>
            
            <label>Confirm Password</label>
            <input type="password" name="confirm_password" required placeholder="Confirm password">
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
        <p class="auth-footer">Already have an account? <a href="<?php echo $base_url; ?>/auth/login.php">Sign In</a></p>
    </div>
</section>

<?php include '../footer.php'; ?>