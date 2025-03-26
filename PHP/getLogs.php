<?php
// PHP/getLogs.php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("
        SELECT LoginNum, StudentName, AdminName, ClassNumber, LoginTime, LogoutTime, SessionStatus
        FROM LoginLogs
        ORDER BY LoginTime DESC
    ");
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(["success" => true, "logs" => $logs]);
    exit;
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
    exit;
}

