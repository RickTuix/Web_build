<?php
// Start session with secure cookie settings FIRST (if not already started)
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// CSRF token generation (if not already set)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : "Taan Tech"; ?></title>
  <link rel="stylesheet" href="/WEBBUILD/stylesheet.css">
  <script src="/WEBBUILD/validation.js"></script>
</head>
<body>

  <!-- Header -->
  <header>
    <div class="container navbar">
      <div class="logo">TAAN TECH</div>
      
      <!-- Right side: navigation links + auth buttons -->
      <div class="nav-right">
        <ul class="nav-links">
          <li><a href="index.php">Home</a></li>
          <li><a href="products.php">Shop</a></li>
          <li><a href="#">Guides</a></li>
          <li><a href="#">About Us</a></li>
          <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="account.php">My Account</a></li>
                <?php if ($_SESSION['role'] === 'admin'): ?>
            <li><a href="admin/index.php">Admin</a></li>
          <?php endif; ?>
         <?php endif; ?>
        </ul>
        
        <div class="header-actions">
          <?php if (isset($_SESSION['user_id'])): ?>
            <!-- Logged in: show Logout -->
            <a href="logout.php" class="btn btn-secondary">Logout</a>
          <?php else: ?>
            <!-- Not logged in: show Sign In and Sign Up links -->
            <a href="login.php" class="btn btn-secondary">Sign In</a>
            <a href="register.php" class="btn btn-primary">Sign Up</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </header>