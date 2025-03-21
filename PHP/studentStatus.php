<?php
// PHP/studentStatus.php

require_once __DIR__ . '/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Use GET parameter "last_name" (as used on the logout page)
    $studentName = trim($_GET['last_name'] ?? '');
    if (!$studentName) {
        echo json_encode(["success" => false, "message" => "No name supplied"]);
        exit;
    }

    // Retrieve active sessions by joining with the Classes table to get the ClassName.
    $stmt = $pdo->prepare("
        SELECT l.LoginNum, l.ClassNum, c.ClassName, l.LoginTime, l.LogoutTime, l.SessionStatus 
        FROM LoginLogs l 
        LEFT JOIN Classes c ON l.ClassNum = c.ClassNum 
        WHERE l.StudentName = :name AND l.SessionStatus = 'IN'
    ");
    $stmt->execute([':name' => $studentName]);
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["success" => true, "sessions" => $sessions]);
    exit;
} else {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
    exit;
}


