<?php
// PHP/db.php
// ===================
// This file establishes a connection to the MySQL database using PDO.
// It should be included in any script that needs DB access.

// NOTE: Store this encryption key securely (outside web root, in env vars, or config files)
$encryptionKey = 'TEAM5'; // Placeholder example ONLY – DO NOT hardcode in production

// Database connection credentials
$host = 'localhost';      // MySQL server host
$db = 'VWLogin';        // Database name
$user = 'root';          // MySQL/MariaDB username (Should match username created while setting up MariaDB)
$pass = '';       // MySQL/MariaDB password (change in production)

// Try connecting to the database using PDO
try {
    // Create new PDO instance with charset utf8mb4 for full Unicode support
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);

    // Configure PDO to throw exceptions when errors occur (great for debugging)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // If connection fails, terminate script and show error
    die("Database connection failed: " . $e->getMessage());
}
