<?php
// PHP/adminLogin.php
// Plain-text password check for testing.
// NOTE: Update to hashed passwords in production.

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/db.php';

// Check if an admin is already logged in.
$checkActive = $pdo->query("
    SELECT 1 FROM LoginLogs 
    WHERE AdminName IS NOT NULL 
      AND SessionStatus = 'IN'
    LIMIT 1
");
if ($checkActive->rowCount() > 0) {
    http_response_code(409);
    echo json_encode(["success" => false, "message" => "An admin is already logged in. Please log out first."]);
    exit;
}

$adminName = trim($_POST['admin_name'] ?? '');
$adminPassword = $_POST['admin_pass'] ?? '';

if (empty($adminName) || empty($adminPassword)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Missing credentials."]);
    exit;
}

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

    // Insert an admin login record (single-row session)
    $logStmt = $pdo->prepare("
        INSERT INTO LoginLogs (AdminName, LoginTime, SessionStatus)
        VALUES (:adminName, NOW(), 'IN')
    ");
    $logStmt->execute([':adminName' => $adminName]);

    $loginNum = $pdo->lastInsertId();
    $_SESSION['adminLoginNum'] = $loginNum;

    echo json_encode(["success" => true, "message" => "Admin login successful."]);
    exit;
} else {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Invalid credentials."]);
    exit;
}
