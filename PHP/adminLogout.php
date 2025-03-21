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

$adminName = $_SESSION['adminName'] ?? null;
$loginNum = $_SESSION['adminLoginNum'] ?? null;

if (!$adminName) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "No admin is logged in."]);
    exit;
}

if (!$loginNum) {
    $stmt = $pdo->prepare("SELECT LoginNum FROM LoginLogs WHERE AdminName = :an AND SessionStatus = 'IN' LIMIT 1");
    $stmt->execute([':an' => $adminName]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $loginNum = $row['LoginNum'];
    } else {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "No active admin session found."]);
        exit;
    }
}

$stmt = $pdo->prepare("
    UPDATE LoginLogs
    SET LogoutTime = NOW(), SessionStatus = 'OUT'
    WHERE LoginNum = :ln AND AdminName = :adminName AND SessionStatus = 'IN'
");
$stmt->execute([':ln' => $loginNum, ':adminName' => $adminName]);

session_unset();
session_destroy();

echo json_encode(["success" => true, "message" => "Admin logged out successfully."]);
exit;


