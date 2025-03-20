<?php
// PHP/autoLogout.php
// This script updates all open sessions (SessionStatus = 'IN')
// to mark them as logged out by setting LogoutTime = NOW() and SessionStatus = 'OUT'

require_once __DIR__ . '/db.php';

try {
    $sql = "UPDATE LoginLogs 
            SET LogoutTime = NOW(), SessionStatus = 'OUT' 
            WHERE SessionStatus = 'IN'";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute()) {
        echo "All open sessions logged out at " . date('Y-m-d H:i:s') . "\n";
    } else {
        echo "Failed to log out open sessions.\n";
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
