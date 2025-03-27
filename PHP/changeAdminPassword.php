<?php
// PHP/changeAdminPassword.php
// ============================
// Handles admin password change requests after 1st login or if password is expired

// Show errors (for development only – disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start session to verify logged-in admin
session_start();
header('Content-Type: application/json'); // Response will be in JSON format

// Require active admin session
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    http_response_code(401); // Unauthorized
    echo json_encode(["success" => false, "message" => "You must be logged in to change your password."]);
    exit;
}

// Include database connection
require_once __DIR__ . '/db.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit;
}

// Retrieve new password value
$newPassword = $_POST['newPassword'] ?? '';

// Validate password is not empty
if (empty($newPassword)) {
    http_response_code(400); // Bad Request
    echo json_encode(["success" => false, "message" => "Password cannot be empty."]);
    exit;
}

// Get current admin from session
$adminName = $_SESSION['adminName'];

// =====================
// Update admin password in the database
// - Store using MD5 hashing
// - Reset PasswordChangeRequired and update LastPasswordChange
// =====================
$hashedPassword = md5($newPassword);
$updateStmt = $pdo->prepare("
    UPDATE Admins
    SET AdminPass = :newPass,
        PasswordChangeRequired = 0,
        LastPasswordChange = NOW()
    WHERE AdminName = :adminName
");

$updateStmt->execute([
    ':newPass' => $hashedPassword,
    ':adminName' => $adminName
]);

// =====================
// Log the new session after password update
// =====================
$_SESSION['admin_logged_in'] = true;
$_SESSION['adminName'] = $adminName;

$logStmt = $pdo->prepare("
    INSERT INTO LoginLogs (AdminName, LoginTime, SessionStatus)
    VALUES (:adminName, NOW(), 'IN')
");
$logStmt->execute([':adminName' => $adminName]);

$_SESSION['adminLoginNum'] = $pdo->lastInsertId();

// Final success response
echo json_encode([
    "success" => true,
    "message" => "Password updated and session started successfully."
]);
exit;
