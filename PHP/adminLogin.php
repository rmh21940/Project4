<?php
// PHP/adminLogin.php
// NOTE: This version compares passwords in plain text. 
//       In production, update to use hashed passwords with password_hash() / password_verify().

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit;
}

$adminName = trim($_POST['admin_name'] ?? '');
$adminPassword = $_POST['admin_pass'] ?? '';

if (empty($adminName) || empty($adminPassword)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Missing credentials."]);
    exit;
}

// Fetch the stored plain-text password for the admin
$stmt = $pdo->prepare("SELECT AdminPass FROM Admins WHERE AdminName = :adminName");
$stmt->execute([':adminName' => $adminName]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Invalid credentials."]);
    exit;
}

if ($adminPassword === $row['AdminPass']) {
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['adminName'] = $adminName;

    // Insert an admin login record into LoginLogs.
    // For admin sessions, StudentName and ClassNum remain NULL.
    $logStmt = $pdo->prepare("
        INSERT INTO LoginLogs (AdminName, LoginTime, SessionStatus)
        VALUES (:adminName, NOW(), 'IN')
    ");
    $logStmt->execute([':adminName' => $adminName]);

    echo json_encode(["success" => true, "message" => "Admin login successful."]);
    exit;
} else {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Invalid credentials."]);
    exit;
}
