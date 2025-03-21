<?php
// PHP/getStudentEnrollments.php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json');

$studentName = trim($_GET['studentName'] ?? '');
if (!$studentName) {
    echo json_encode(["success" => false, "message" => "No student name provided."]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT e.ClassNum, c.ClassName
    FROM Enrollments e
    JOIN Classes c ON e.ClassNum = c.ClassNum
    WHERE e.StudentName = :sn
    ORDER BY c.ClassName ASC
");
$stmt->execute([':sn' => $studentName]);
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(["success" => true, "classes" => $classes]);
exit;

