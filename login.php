<?php
// Start session with secure cookie settings FIRST
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

// Initialize error variable
$error = '';

// CSRF token generation (if not already set)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        die("Invalid CSRF token.");
    }

    $login = trim($_POST['username']); // can be username or email
    $password = $_POST['password'];

    // Brute‑force protection
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE (username = ? OR email = ?) AND attempted_at > (NOW() - INTERVAL 900 SECOND)");
    $stmt->execute([$login, $login]);
    if ($stmt->fetchColumn() >= 5) {
        $error = "Too many failed attempts. Try again in 15 minutes.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$login, $login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Successful login
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            // Clear attempts
            $pdo->prepare("DELETE FROM login_attempts WHERE username = ? OR email = ?")->execute([$login, $login]);
            header("Location: index.php");
            exit;
        } else {
            // Record failed attempt
            $pdo->prepare("INSERT INTO login_attempts (username, ip_address) VALUES (?, ?)")
                ->execute([$login, $_SERVER['REMOTE_ADDR']]);
            $error = "Invalid credentials.";
        }
    }
}

// Set page title
$pageTitle = "Sign In - Taan Tech";
include 'header.php';
?>

<!-- Login Section with same background as hero -->
<section class="auth-section">
    <div class="auth-card">
        <h2>Sign In</h2>
        <?php if ($error): ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <label>Username or Email</label>
            <input type="text" name="username" required placeholder="Enter username or email">
            <label>Password</label>
            <input type="password" name="password" required placeholder="Enter password">
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <div class="google-login">
            <button class="btn btn-google" onclick="alert('Google login coming soon!')">
                <img src="Images/google-icon.png" alt="Google" style="width:20px; vertical-align:middle;"> Sign in with Google
            </button>
        </div>
        <p class="auth-footer">Don't have an account? <a href="register.php">Sign Up</a></p>
    </div>
</section>

<?php include 'footer.php'; ?>