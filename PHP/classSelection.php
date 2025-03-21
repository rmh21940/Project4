<?php
// PHP/classSelection.php

// Enable error reporting for debugging (remove or disable in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set header for JSON response
header('Content-Type: application/json');

// Include the database connection file
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $studentName = trim($_GET['studentName'] ?? '');
    if (!$studentName) {
        http_response_code(400);
        echo json_encode([
            "success" => false,
            "message" => "Missing studentName"
        ]);
        exit;
    }

    try {
        // Fetch classes for this student
        $stmt = $pdo->prepare("
            SELECT 
                 c.ClassNum AS classNum,
                 c.ClassName AS className
            FROM Enrollments e
            JOIN Classes c ON e.ClassNum = c.ClassNum
            WHERE e.StudentName = :studentName

        ");
        $stmt->execute([':studentName' => $studentName]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($results) {
            // Return success + array of classes
            echo json_encode([
                "success" => true,
                "classes" => $results
            ]);
        } else {
            // Return an error if no classes found
            echo json_encode([
                "success" => false,
                "message" => "No classes found for $studentName"
            ]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => "Database error: " . $e->getMessage()
        ]);
    }
} else {
    // Wrong HTTP method
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed"
    ]);
}


