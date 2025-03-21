<?php
// PHP/adminDashboard.php

require_once __DIR__ . '/db.php';

$action = $_REQUEST['action'] ?? '';

// Download Report action remains unchanged.
if ($action === 'downloadReport') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="login_report.csv"');

    $stmt = $pdo->query("
        SELECT LoginNum, StudentName, AdminName, ClassNum, LoginTime, LogoutTime, SessionStatus
        FROM LoginLogs
        ORDER BY LoginTime DESC
    ");
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $output = fopen('php://output', 'w');
    if (!empty($logs)) {
        fputcsv($output, array_keys($logs[0]));
        foreach ($logs as $row) {
            fputcsv($output, $row);
        }
    } else {
        fputcsv($output, ['No data available']);
    }
    fclose($output);
    exit;
}

header('Content-Type: application/json');

try {
    switch ($action) {
        case 'addStudent':
            // Add student without enrollment.
            $studentName = trim($_POST['studentName'] ?? '');
            if (!$studentName) {
                echo json_encode(["success" => false, "message" => "Missing student name."]);
                exit;
            }
            $check = $pdo->prepare("SELECT 1 FROM Students WHERE StudentName = :sn LIMIT 1");
            $check->execute([':sn' => $studentName]);
            if ($check->rowCount() > 0) {
                echo json_encode(["success" => false, "message" => "Student already exists."]);
                exit;
            }
            $stmt = $pdo->prepare("INSERT INTO Students (StudentName) VALUES (:sn)");
            if ($stmt->execute([':sn' => $studentName])) {
                echo json_encode(["success" => true, "message" => "Student added successfully."]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to add student."]);
            }
            break;

        case 'enrollStudent':
            // Enroll an existing student in a new class.
            $studentName = trim($_POST['studentName'] ?? '');
            $classNum = trim($_POST['classNum'] ?? '');
            if (!$studentName || !$classNum) {
                echo json_encode(["success" => false, "message" => "Missing student name or class number."]);
                exit;
            }
            // Ensure student exists.
            $check = $pdo->prepare("SELECT 1 FROM Students WHERE StudentName = :sn LIMIT 1");
            $check->execute([':sn' => $studentName]);
            if ($check->rowCount() === 0) {
                echo json_encode(["success" => false, "message" => "Student does not exist. Please add the student first."]);
                exit;
            }
            // Verify class exists.
            $classCheck = $pdo->prepare("SELECT 1 FROM Classes WHERE ClassNum = :cn LIMIT 1");
            $classCheck->execute([':cn' => $classNum]);
            if ($classCheck->rowCount() === 0) {
                echo json_encode(["success" => false, "message" => "Selected class does not exist."]);
                exit;
            }
            // Check if already enrolled.
            $enrollCheck = $pdo->prepare("SELECT 1 FROM Enrollments WHERE StudentName = :sn AND ClassNum = :cn LIMIT 1");
            $enrollCheck->execute([':sn' => $studentName, ':cn' => $classNum]);
            if ($enrollCheck->rowCount() > 0) {
                echo json_encode(["success" => false, "message" => "Student is already enrolled in that class."]);
                exit;
            }
            $insertEnroll = $pdo->prepare("INSERT INTO Enrollments (StudentName, ClassNum) VALUES (:sn, :cn)");
            if ($insertEnroll->execute([':sn' => $studentName, ':cn' => $classNum])) {
                echo json_encode(["success" => true, "message" => "Student enrolled in class successfully."]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to enroll student in the class."]);
            }
            break;

        case 'unenrollStudent':
            // Unenroll a student from one class.
            $studentName = trim($_POST['studentName'] ?? '');
            $classNum = trim($_POST['classNum'] ?? '');
            if (!$studentName || !$classNum) {
                echo json_encode(["success" => false, "message" => "Missing student name or class number."]);
                exit;
            }
            $enrollCheck = $pdo->prepare("SELECT 1 FROM Enrollments WHERE StudentName = :sn AND ClassNum = :cn");
            $enrollCheck->execute([':sn' => $studentName, ':cn' => $classNum]);
            if ($enrollCheck->rowCount() === 0) {
                echo json_encode(["success" => false, "message" => "Student is not enrolled in that class."]);
                exit;
            }
            $delEnroll = $pdo->prepare("DELETE FROM Enrollments WHERE StudentName = :sn AND ClassNum = :cn");
            if ($delEnroll->execute([':sn' => $studentName, ':cn' => $classNum])) {
                echo json_encode(["success" => true, "message" => "Student unenrolled from class successfully."]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to unenroll student from class."]);
            }
            break;

        case 'deleteStudent':
            // Delete student entirely (all enrollments and student record).
            $studentName = trim($_POST['studentName'] ?? '');
            if (!$studentName) {
                echo json_encode(["success" => false, "message" => "Missing student name."]);
                exit;
            }
            $check = $pdo->prepare("SELECT 1 FROM Students WHERE StudentName = :sn LIMIT 1");
            $check->execute([':sn' => $studentName]);
            if ($check->rowCount() === 0) {
                echo json_encode(["success" => false, "message" => "Student not found."]);
                exit;
            }
            // Delete enrollments
            $delEnroll = $pdo->prepare("DELETE FROM Enrollments WHERE StudentName = :sn");
            $delEnroll->execute([':sn' => $studentName]);
            // Delete student
            $stmt = $pdo->prepare("DELETE FROM Students WHERE StudentName = :sn");
            if ($stmt->execute([':sn' => $studentName])) {
                echo json_encode(["success" => true, "message" => "Student deleted successfully."]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to delete student."]);
            }
            break;

        case 'addClass':
            // Add a new class.
            $className = trim($_POST['className'] ?? '');
            $classNum = trim($_POST['classNum'] ?? '');
            if (!$className || !$classNum) {
                echo json_encode(["success" => false, "message" => "Missing class name or class number."]);
                exit;
            }
            // Validate the class number format: expect 3 uppercase letters followed by 3 digits.
            if (!preg_match('/^[A-Z]{3}[0-9]{3}$/', $classNum)) {
                echo json_encode(["success" => false, "message" => "Invalid class number format. Expected format: XXX999 (e.g., CSC101)."]);
                exit;
            }
            // Check if class already exists.
            $check = $pdo->prepare("SELECT 1 FROM Classes WHERE ClassNum = :cn LIMIT 1");
            $check->execute([':cn' => $classNum]);
            if ($check->rowCount() > 0) {
                echo json_encode(["success" => false, "message" => "Class number already exists."]);
                exit;
            }
            $stmt = $pdo->prepare("INSERT INTO Classes (ClassNum, ClassName) VALUES (:cn, :cname)");
            if ($stmt->execute([':cn' => $classNum, ':cname' => $className])) {
                echo json_encode(["success" => true, "message" => "Class added successfully."]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to add class."]);
            }
            break;

        case 'deleteClass':
            // Delete a class.
            $classNum = trim($_POST['classNum'] ?? '');
            if (!$classNum) {
                echo json_encode(["success" => false, "message" => "Missing class number."]);
                exit;
            }
            // Check if class exists.
            $check = $pdo->prepare("SELECT 1 FROM Classes WHERE ClassNum = :cn LIMIT 1");
            $check->execute([':cn' => $classNum]);
            if ($check->rowCount() === 0) {
                echo json_encode(["success" => false, "message" => "Class number not found."]);
                exit;
            }
            // Delete enrollments for the class.
            $delEnroll = $pdo->prepare("DELETE FROM Enrollments WHERE ClassNum = :cn");
            $delEnroll->execute([':cn' => $classNum]);
            // Delete the class.
            $stmt = $pdo->prepare("DELETE FROM Classes WHERE ClassNum = :cn");
            if ($stmt->execute([':cn' => $classNum])) {
                echo json_encode(["success" => true, "message" => "Class deleted successfully."]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to delete class."]);
            }
            break;

        default:
            echo json_encode(["success" => false, "message" => "Invalid or missing 'action' parameter."]);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
