<?php
/** @var string $base_url This is defined in db.php */
// ... rest of code
// Start session (if not already started)
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

// Note: $base_url is now defined in db.php (included before header.php)
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

  <header>
    <div class="container navbar">
      <a href="<?php echo $base_url; ?>/index.php" class="logo">TAAN TECH</a>
      
      <div class="nav-right">
        <ul class="nav-links">
          <li><a href="<?php echo $base_url; ?>/index.php">Home</a></li>
          <li><a href="<?php echo $base_url; ?>/products.php">Shop</a></li>
          <li><a href="<?php echo $base_url; ?>/index.php#services">Services</a></li>
          <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="<?php echo $base_url; ?>/checkout/cart.php">My Cart</a></li>
          <?php else: ?>
            <li><a href="<?php echo $base_url; ?>/index.php#about">About Us</a></li>
          <?php endif; ?>
          <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="<?php echo $base_url; ?>/account/account.php">My Account</a></li>
                <?php if ($_SESSION['role'] === 'admin'): ?>
            <li><a href="<?php echo $base_url; ?>/admin/index.php">Admin</a></li>
          <?php endif; ?>
         <?php endif; ?>
        </ul>
        
        <div class="header-actions">
          <?php if (isset($_SESSION['user_id'])): ?>
            <a href="<?php echo $base_url; ?>/auth/logout.php" class="btn btn-secondary">Logout</a>
          <?php else: ?>
            <a href="<?php echo $base_url; ?>/auth/login.php" class="btn btn-secondary">Sign In</a>
            <a href="<?php echo $base_url; ?>/auth/register.php" class="btn btn-primary">Sign Up</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </header>