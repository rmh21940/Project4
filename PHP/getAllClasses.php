<?php
// PHP/getAllClasses.php
// ==========================
// This script retrieves all available classes from the database,
// ordered alphabetically by class name, and returns them as JSON.

// Include database connection
require_once __DIR__ . '/db.php';

// Set response type to JSON
header('Content-Type: application/json');

// Query all class numbers and names, ordered by class name (A-Z)
$stmt = $pdo->query("
    SELECT ClassNum, ClassName
    FROM Classes
    ORDER BY ClassName ASC
");

// Fetch all class records as an associative array
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return the class list in a JSON response
echo json_encode([
    "success" => true,
    "classes" => $classes
]);

exit;
