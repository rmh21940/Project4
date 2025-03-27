<?php
// PHP/studentStatus.php
// ==========================================
// This script checks if a student currently has any active login sessions.
// It is useful for dashboards or to determine if logout is needed.

// Include the database connection
require_once __DIR__ . '/db.php';

// Return JSON-formatted response
header('Content-Type: application/json');

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // Get the student's name from the query string (key = 'last_name')
    $studentName = trim($_GET['last_name'] ?? '');

    // Reject request if no name provided
    if (!$studentName) {
        echo json_encode([
            "success" => false,
            "message" => "No name supplied"
        ]);
        exit;
    }

    // Query active sessions (SessionStatus = 'IN') for this student
    // Join with Classes to include class name in the response
    $stmt = $pdo->prepare("
        SELECT 
            l.LoginNum,       -- Session ID
            l.ClassNum,       -- Class identifier
            c.ClassName,      -- Readable class name (from Classes table)
            l.LoginTime,      -- Time login occurred
            l.LogoutTime,     -- Should be NULL for active sessions
            l.SessionStatus   -- Should be 'IN' for active
        FROM LoginLogs l 
        LEFT JOIN Classes c ON l.ClassNum = c.ClassNum 
        WHERE l.StudentName = :name AND l.SessionStatus = 'IN'
    ");

    $stmt->execute([':name' => $studentName]);
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return session details (if any)
    echo json_encode([
        "success" => true,
        "sessions" => $sessions
    ]);
    exit;

} else {
    // Method not allowed
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed"
    ]);
    exit;
}
