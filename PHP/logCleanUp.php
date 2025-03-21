<?php
// PHP/logCleanUp.php
// This script deletes LoginLogs entries older than 90 days.
// Intended to be run via a cron job if the MySQL event scheduler cannot be used.

require_once __DIR__ . '/db.php';  // Adjust the path if necessary

// Optionally set the content type for logging/debugging output
header('Content-Type: text/plain');

try {
    // Delete records older than 90 days from the VWLogin.LoginLogs table
    $stmt = $pdo->prepare("DELETE FROM VWLogin.LoginLogs WHERE LoginTime < NOW() - INTERVAL 90 DAY");
    $stmt->execute();

    $deletedRows = $stmt->rowCount();
    echo "Log cleanup completed. Deleted {$deletedRows} rows.\n";
} catch (PDOException $e) {
    echo "Error during log cleanup: " . $e->getMessage() . "\n";
}
