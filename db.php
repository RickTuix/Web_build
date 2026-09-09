<?php
// Include helper functions
require_once __DIR__ . '/functions.php';

// Start session (if not already started)
ensure_session();

// Determine base URL for links (works from both root and subfolders)
$script_name = $_SERVER['SCRIPT_NAME'];
$base_url = rtrim(dirname($script_name), '/');

// If we're in a subfolder (admin, auth, account, checkout, etc.), go up one level
$current_folder = basename(dirname($script_name));
if (in_array($current_folder, ['admin', 'auth', 'account', 'checkout'])) {
    $base_url = dirname($base_url);
}

// Database configuration
$host = 'localhost';
$db   = 'taantech_db';
$user = 'root';
$pass = 'root';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    error_log($e->getMessage());
    die("Database connection failed.");
}

// Return the PDO instance
return $pdo;