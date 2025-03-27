<?php
// PHP/adminLogout.php
// =====================
// This script handles admin logout requests.
// It updates the session record in the database and clears session data.

session_start();
header('Content-Type: application/json'); // Return response in JSON format

require_once __DIR__ . '/db.php'; // Load database connection

// Only allow POST requests to logout
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit;
}

// Get current session's admin details (if any)
$adminName = $_SESSION['adminName'] ?? null;
$loginNum = $_SESSION['adminLoginNum'] ?? null;

// If no admin is logged in, reject the request
if (!$adminName) {
    http_response_code(400); // Bad Request
    echo json_encode(["success" => false, "message" => "No admin is logged in."]);
    exit;
}

// If login number is not available in session, try to fetch it from LoginLogs
if (!$loginNum) {
    $stmt = $pdo->prepare("
        SELECT LoginNum
        FROM LoginLogs
        WHERE AdminName = :an AND SessionStatus = 'IN'
        LIMIT 1
    ");
    $stmt->execute([':an' => $adminName]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $loginNum = $row['LoginNum'];
    } else {
        // No active login session found in database
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "No active admin session found."]);
        exit;
    }
}

// Update login session in DB to mark it as logged out
$stmt = $pdo->prepare("
    UPDATE LoginLogs
    SET LogoutTime = NOW(), SessionStatus = 'OUT'
    WHERE LoginNum = :ln AND AdminName = :adminName AND SessionStatus = 'IN'
");
$stmt->execute([
    ':ln' => $loginNum,
    ':adminName' => $adminName
]);

// Clear all session variables and destroy the session
session_unset();
session_destroy();

// Successful logout response
echo json_encode(["success" => true, "message" => "Admin logged out successfully."]);
exit;



