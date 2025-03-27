<?php
// PHP/classSelection.php
// ===========================
// This script fetches the list of classes a specific student is enrolled in,
// based on the student name provided via a GET request.

// Enable error reporting during development (❗Remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set JSON as the content type of the response
header('Content-Type: application/json');

// Load database connection
require_once __DIR__ . '/db.php';

// Only handle GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // Get the student name from the query string, and trim spaces
    $studentName = trim($_GET['studentName'] ?? '');

    // Reject if no student name provided
    if (!$studentName) {
        http_response_code(400); // Bad Request
        echo json_encode([
            "success" => false,
            "message" => "Missing studentName"
        ]);
        exit;
    }

    try {
        // Fetch all class numbers and names for the given student
        $stmt = $pdo->prepare("
            SELECT 
                 c.ClassNum AS classNum,
                 c.ClassName AS className
            FROM Enrollments e
            JOIN Classes c ON e.ClassNum = c.ClassNum
            WHERE e.StudentName = :studentName
        ");
        $stmt->execute([':studentName' => $studentName]);

        // Fetch all matching class records
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($results) {
            // Return success with the array of classes
            echo json_encode([
                "success" => true,
                "classes" => $results
            ]);
        } else {
            // Student is not enrolled in any classes
            echo json_encode([
                "success" => false,
                "message" => "No classes found for $studentName"
            ]);
        }
    } catch (PDOException $e) {
        // Handle any database-related errors
        http_response_code(500); // Internal Server Error
        echo json_encode([
            "success" => false,
            "message" => "Database error: " . $e->getMessage()
        ]);
    }

} else {
    // Method not allowed (must be GET)
    http_response_code(405); // Method Not Allowed
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed"
    ]);
}

