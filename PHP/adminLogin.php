<?php
// PHP/adminLogin.php
// ===================
// Handles admin login requests.

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/db.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit;
}

// Get input and sanitize
$adminName = trim($_POST['admin_name'] ?? '');
$adminPassword = $_POST['admin_pass'] ?? '';

if (empty($adminName) || empty($adminPassword)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Missing credentials."]);
    exit;
}

// Check for existing active session
$stmtActive = $pdo->prepare("SELECT LoginNum FROM LoginLogs WHERE AdminName = :adminName AND SessionStatus = 'IN' LIMIT 1");
$stmtActive->execute([':adminName' => $adminName]);
$activeSession = $stmtActive->fetch(PDO::FETCH_ASSOC);

if ($activeSession) {
    http_response_code(409);
    echo json_encode([
        "success" => false,
        "alreadyLoggedIn" => true,
        "message" => "An admin is already logged in. Would you like to force logout and re-login, or proceed to the dashboard?"
    ]);
    exit;
}

// Fetch admin credentials and settings
$stmt = $pdo->prepare("SELECT AdminPass, PasswordChangeRequired, LastPasswordChange FROM Admins WHERE AdminName = :adminName");
$stmt->execute([':adminName' => $adminName]);
$adminRow = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$adminRow) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Invalid credentials."]);
    exit;
}

// Validate password
if (md5($adminPassword) !== $adminRow['AdminPass']) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Invalid credentials."]);
    exit;
}

// Password expiration and first-time logic
$forceReset = (int) $adminRow['PasswordChangeRequired'] === 1;
$lastChanged = new DateTime($adminRow['LastPasswordChange']);
$now = new DateTime();
$daysSinceChange = $lastChanged->diff($now)->days;

if ($forceReset || $daysSinceChange > 90) {
    $_SESSION['admin_pending_change'] = $adminName;
    echo json_encode([
        "success" => false,
        "passwordExpired" => true,
        "message" => "Password expired. You must change it to continue."
    ]);
    exit;
}

// Calculate days until expiration
$daysUntilExpiration = 90 - $daysSinceChange;

// Begin login process
$_SESSION['admin_logged_in'] = true;
$_SESSION['adminName'] = $adminName;

// Record login in LoginLogs
$logStmt = $pdo->prepare("
    INSERT INTO LoginLogs (AdminName, LoginTime, SessionStatus)
    VALUES (:adminName, NOW(), 'IN')
");
$logStmt->execute([':adminName' => $adminName]);

$loginNum = $pdo->lastInsertId();
$_SESSION['adminLoginNum'] = $loginNum;

// Return response
$response = [
    "success" => true,
    "message" => "Admin login successful."
];

// Add warning if password is expiring within 7 days
if ($daysUntilExpiration <= 7 && $daysUntilExpiration > 0) {
    $response["passwordExpiringSoon"] = true;
    $response["daysLeft"] = $daysUntilExpiration;
}

echo json_encode($response);
exit;
