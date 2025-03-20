<?php
// PHP/adminLogout.php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit;
}

$adminName = trim($_POST['admin_name'] ?? '');
$loginNum = trim($_POST['loginNum'] ?? '');

if (empty($adminName) || empty($loginNum)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Missing admin name or login number."]);
    exit;
}

// Verify the provided loginNum corresponds to an active admin session.
$sql = "SELECT LoginNum FROM LoginLogs WHERE LoginNum = :ln AND AdminName = :an AND SessionStatus = 'IN' LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([':ln' => $loginNum, ':an' => $adminName]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$existing) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "No matching active session found for admin."]);
    exit;
}

// Update the row: set LogoutTime to NOW() and change SessionStatus to 'OUT'
$update = $pdo->prepare("
    UPDATE LoginLogs
    SET LogoutTime = NOW(),
        SessionStatus = 'OUT'
    WHERE LoginNum = :ln AND AdminName = :an AND SessionStatus = 'IN'
");
$update->execute([':ln' => $loginNum, ':an' => $adminName]);

echo json_encode(["success" => true, "message" => "Admin logout successful. Session ID {$loginNum} closed."]);
exit;

