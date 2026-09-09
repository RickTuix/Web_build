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

// Determine base URL for links (works from both root and admin subfolder)
$script_name = $_SERVER['SCRIPT_NAME'];
$base_url = rtrim(dirname($script_name), '/');
if (basename(dirname($script_name)) === 'admin') {
    $base_url = dirname($base_url);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : "Taan Tech"; ?></title>
  <link rel="stylesheet" href="<?php echo $base_url; ?>/stylesheet.css">
  <script src="<?php echo $base_url; ?>/validation.js"></script>
</head>
<body>

  <!-- Header -->
  <header>
    <div class="container navbar">
      <div class="logo">TAAN TECH</div>
      
      <!-- Right side: navigation links + auth buttons -->
      <div class="nav-right">
        <ul class="nav-links">
          <li><a href="<?php echo $base_url; ?>/index.php">Home</a></li>
          <li><a href="<?php echo $base_url; ?>/products.php">Shop</a></li>
          <li><a href="#">Guides</a></li>
          <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="<?php echo $base_url; ?>/cart.php">My Cart</a></li>
          <?php else: ?>
            <li><a href="#">About Us</a></li>
          <?php endif; ?>
          <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="<?php echo $base_url; ?>/account.php">My Account</a></li>
                <?php if ($_SESSION['role'] === 'admin'): ?>
            <li><a href="<?php echo $base_url; ?>/admin/index.php">Admin</a></li>
          <?php endif; ?>
         <?php endif; ?>
        </ul>
        
        <div class="header-actions">
          <?php if (isset($_SESSION['user_id'])): ?>
            <!-- Logged in: show Logout -->
            <a href="<?php echo $base_url; ?>/logout.php" class="btn btn-secondary">Logout</a>
          <?php else: ?>
            <!-- Not logged in: show Sign In and Sign Up links -->
            <a href="<?php echo $base_url; ?>/login.php" class="btn btn-secondary">Sign In</a>
            <a href="<?php echo $base_url; ?>/register.php" class="btn btn-primary">Sign Up</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </header>