<?php
// PHP/studentLogin.php

require_once __DIR__ . '/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed."]);
    exit;
}

$studentName = trim($_POST['last_name'] ?? '');
$classNum = trim($_POST['class'] ?? '');

if (!$studentName || !$classNum) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Missing student name or class."]);
    exit;
}

// Optional: Check if the student is enrolled in that class
$check = $pdo->prepare("SELECT 1 FROM Enrollments WHERE StudentName = :sn AND ClassNum = :cn");
$check->execute([':sn' => $studentName, ':cn' => $classNum]);
if ($check->rowCount() === 0) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Student not enrolled in this class or not found."]);
    exit;
}

// Insert a new row with SessionStatus='IN', LogoutTime = NULL
$insert = $pdo->prepare("
    INSERT INTO LoginLogs (StudentName, ClassNum, SessionStatus)
    VALUES (:sn, :cn, 'IN')
");
$insert->execute([
    ':sn' => $studentName,
    ':cn' => $classNum
]);

$loginNum = $pdo->lastInsertId();
echo json_encode([
    "success" => true,
    "message" => "Login successful.",
    "loginNum" => $loginNum
]);
exit;

