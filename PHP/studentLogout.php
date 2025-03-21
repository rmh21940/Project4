<?php
// PHP/studentLogout.php

require_once __DIR__ . '/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
    exit;
}

$studentName = trim($_POST['last_name'] ?? '');
$loginNum = trim($_POST['loginNum'] ?? '');

if (!$studentName || !$loginNum) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Missing student name or login number."]);
    exit;
}

// Verify that the provided loginNum corresponds to an active session for this student.
$sql = "SELECT LoginNum FROM LoginLogs WHERE LoginNum = :ln AND StudentName = :sn AND SessionStatus = 'IN' LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':ln' => $loginNum,
    ':sn' => $studentName
]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$existing) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "No matching active session found."]);
    exit;
}

// Update the session row: set LogoutTime to NOW() and change SessionStatus to 'OUT'
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

echo json_encode([
    "success" => true,
    "message" => "Logout successful. Session ID {$loginNum} closed."
]);
exit;


