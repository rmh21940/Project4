<?php
// PHP/getStudentEnrollments.php
// ======================================
// This script retrieves all classes a specific student is enrolled in,
// based on the student's name passed via GET request.

// Include database connection
require_once __DIR__ . '/db.php';

// Set the response type to JSON
header('Content-Type: application/json');

// Get the student name from the query string and remove extra spaces
$studentName = trim($_GET['studentName'] ?? '');

// ❌ If no student name is provided, return an error
if (!$studentName) {
    echo json_encode([
        "success" => false,
        "message" => "No student name provided."
    ]);
    exit;
}

// 🔍 Query to get the class numbers and names for this student
$stmt = $pdo->prepare("
    SELECT 
        e.ClassNum,      -- Class identifier
        c.ClassName      -- Readable class name
    FROM Enrollments e
    JOIN Classes c ON e.ClassNum = c.ClassNum
    WHERE e.StudentName = :sn
    ORDER BY c.ClassName ASC
");

// Execute query with provided student name
$stmt->execute([':sn' => $studentName]);

// Fetch results as associative array
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Return list of enrolled classes in JSON format
echo json_encode([
    "success" => true,
    "classes" => $classes
]);
exit;
