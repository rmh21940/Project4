<?php
// PHP/getLogs.php
// ==============================
// This script retrieves login activity logs from the system,
// including who logged in, when, and session status (active or logged out).

// Include the database connection
require_once __DIR__ . '/db.php';

// Set response to JSON format
header('Content-Type: application/json');

try {
    // Fetch all login records from LoginLogs, most recent first
    $stmt = $pdo->query("
        SELECT 
            LoginNum,         -- Unique session ID
            StudentName,      -- Name of student (if any)
            AdminName,        -- Name of admin who logged in
            ClassNumber,      -- Optional: class tied to session
            LoginTime,        -- When login occurred
            LogoutTime,       -- When logout occurred (nullable if still active)
            SessionStatus     -- Status: 'IN' = active, 'OUT' = ended
        FROM LoginLogs
        ORDER BY LoginTime DESC
    ");

    // Fetch results as associative array
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return logs in JSON format
    echo json_encode([
        "success" => true,
        "logs" => $logs
    ]);
    exit;

} catch (PDOException $e) {
    // Handle DB errors gracefully
    http_response_code(500); // Internal Server Error
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
    exit;
}
