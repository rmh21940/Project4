<?php
// PHP/adminLogin.php
// NOTE: This version uses plain-text password checking for testing.
//       For production, switch to hashed passwords.

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/db.php';

// Ensure the request method is POST.
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

// Check if an active session exists for this admin.
$stmtActive = $pdo->prepare("SELECT LoginNum FROM LoginLogs WHERE AdminName = :adminName AND SessionStatus = 'IN' LIMIT 1");
$stmtActive->execute([':adminName' => $adminName]);
$activeSession = $stmtActive->fetch(PDO::FETCH_ASSOC);

if ($activeSession) {
    // Instead of outright failure, return a flag so client can offer options.
    http_response_code(409);
    echo json_encode([
        "success" => false,
        "alreadyLoggedIn" => true,
        "message" => "An admin is already logged in. Would you like to force logout and re-login, or proceed to the dashboard?"
    ]);
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
    $logStmt = $pdo->prepare("INSERT INTO LoginLogs (AdminName, LoginTime, SessionStatus) VALUES (:adminName, NOW(), 'IN')");
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

