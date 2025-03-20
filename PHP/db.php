<?php
// PHP/db.php
$encryptionKey = 'TEAM5'; // Example only—store securely in production

$host = 'localhost';
$db = 'VWLogin';
$user = 'root';
$pass = ''; // Update if necessary

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    // Set error mode to exception for easier debugging
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
