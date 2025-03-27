<?php
// PHP/studentLogout.php
// ============================================
// This script handles student logout requests.
// It verifies an active session exists and updates the LoginLogs
// to mark the session as ended (SessionStatus = 'OUT').

// Include database connection
require_once __DIR__ . '/db.php';

// Set response format to JSON
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
    exit;
}

// Retrieve student name and session ID (login number) from request
$studentName = trim($_POST['last_name'] ?? '');
$loginNum = trim($_POST['loginNum'] ?? '');

// Validate that both values are present
if (!$studentName || !$loginNum) {
    http_response_code(400); // Bad Request
    echo json_encode(["success" => false, "message" => "Missing student name or login number."]);
    exit;
}

// Check if this session is still active (SessionStatus = 'IN')
$sql = "
    SELECT LoginNum
    FROM LoginLogs
    WHERE LoginNum = :ln
      AND StudentName = :sn
      AND SessionStatus = 'IN'
    LIMIT 1
";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':ln' => $loginNum,
    ':sn' => $studentName
]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

// If no matching active session found, reject the logout request
if (!$existing) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "No matching active session found."]);
    exit;
}

// Update the login record to mark the session as ended
$update = $pdo->prepare("
    UPDATE LoginLogs
    SET LogoutTime = NOW(),
        SessionStatus = 'OUT'
    WHERE LoginNum = :ln
      AND StudentName = :sn
      AND SessionStatus = 'IN'
");
$update->execute([
    ':ln' => $loginNum,
    ':sn' => $studentName
]);

// Return success response
echo json_encode([
    "success" => true,
    "message" => "Logout successful. Session ID {$loginNum} closed."
]);
exit;


