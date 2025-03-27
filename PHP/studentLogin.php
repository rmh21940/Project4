<?php
// PHP/studentLogin.php
// =====================================
// This script handles student logins.
// It validates the student's name and class number,
// checks their enrollment status, and logs the session.

// Include the database connection
require_once __DIR__ . '/db.php';

// Set response format to JSON
header('Content-Type: application/json');

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["success" => false, "message" => "Method not allowed."]);
    exit;
}

// Get student name and class number from POST request
$studentName = trim($_POST['last_name'] ?? '');  // NOTE: 'last_name' is assumed to be the student identifier
$classNum = trim($_POST['class'] ?? '');          // Class number the student is trying to log into

// Reject if either field is empty
if (!$studentName || !$classNum) {
    http_response_code(400); // Bad Request
    echo json_encode(["success" => false, "message" => "Missing student name or class."]);
    exit;
}

// Check if the student is actually enrolled in the given class
$check = $pdo->prepare("
    SELECT 1
    FROM Enrollments
    WHERE StudentName = :sn AND ClassNum = :cn
");
$check->execute([
    ':sn' => $studentName,
    ':cn' => $classNum
]);

// If not enrolled, reject the login
if ($check->rowCount() === 0) {
    http_response_code(401); // Unauthorized
    echo json_encode(["success" => false, "message" => "Student not enrolled in this class or not found."]);
    exit;
}

// Insert login record into LoginLogs
// SessionStatus will be 'IN' to indicate an active session
// LogoutTime remains NULL until student logs out
$insert = $pdo->prepare("
    INSERT INTO LoginLogs (StudentName, ClassNum, SessionStatus)
    VALUES (:sn, :cn, 'IN')
");
$insert->execute([
    ':sn' => $studentName,
    ':cn' => $classNum
]);

// Get the newly created login session ID
$loginNum = $pdo->lastInsertId();

// Return successful login response
echo json_encode([
    "success" => true,
    "message" => "Login successful.",
    "loginNum" => $loginNum
]);
exit;
