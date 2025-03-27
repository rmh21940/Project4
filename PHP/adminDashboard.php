<?php
// PHP/adminDashboard.php

// Load database connection settings
require_once __DIR__ . '/db.php';

// Get action from request (GET or POST), fallback to empty string if not set
$action = $_REQUEST['action'] ?? '';

// === DOWNLOAD LOGIN REPORT AS CSV ===
if ($action === 'downloadReport') {
    // Set headers so the response is treated as a CSV file download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="login_report.csv"');

    // Retrieve login logs, most recent first
    $stmt = $pdo->query("
        SELECT LoginNum, StudentName, AdminName, ClassNum, LoginTime, LogoutTime, SessionStatus
        FROM LoginLogs
        ORDER BY LoginTime DESC
    ");
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Output data as CSV
    $output = fopen('php://output', 'w');
    if (!empty($logs)) {
        // Add header row (column names)
        fputcsv($output, array_keys($logs[0]));
        // Add all log rows
        foreach ($logs as $row) {
            fputcsv($output, $row);
        }
    } else {
        // If no data, add message row
        fputcsv($output, ['No data available']);
    }
    fclose($output);
    exit;
}

// Default response is JSON
header('Content-Type: application/json');

try {
    switch ($action) {

        case 'addStudent':
            // === Add a new student (not enrolled in any class yet) ===
            $studentName = trim($_POST['studentName'] ?? '');
            if (!$studentName) {
                echo json_encode(["success" => false, "message" => "Missing student name."]);
                exit;
            }

            // Check if student already exists
            $check = $pdo->prepare("SELECT 1 FROM Students WHERE StudentName = :sn LIMIT 1");
            $check->execute([':sn' => $studentName]);
            if ($check->rowCount() > 0) {
                echo json_encode(["success" => false, "message" => "Student already exists."]);
                exit;
            }

            // Insert student record into Students table
            $stmt = $pdo->prepare("INSERT INTO Students (StudentName) VALUES (:sn)");
            if ($stmt->execute([':sn' => $studentName])) {
                echo json_encode(["success" => true, "message" => "Student added successfully."]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to add student."]);
            }
            break;

        case 'enrollStudent':
            // === Enroll a student in a specific class ===
            $studentName = trim($_POST['studentName'] ?? '');
            $classNum = trim($_POST['classNum'] ?? '');

            if (!$studentName || !$classNum) {
                echo json_encode(["success" => false, "message" => "Missing student name or class number."]);
                exit;
            }

            // Confirm student exists
            $check = $pdo->prepare("SELECT 1 FROM Students WHERE StudentName = :sn LIMIT 1");
            $check->execute([':sn' => $studentName]);
            if ($check->rowCount() === 0) {
                echo json_encode(["success" => false, "message" => "Student does not exist. Please add the student first."]);
                exit;
            }

            // Confirm class exists
            $classCheck = $pdo->prepare("SELECT 1 FROM Classes WHERE ClassNum = :cn LIMIT 1");
            $classCheck->execute([':cn' => $classNum]);
            if ($classCheck->rowCount() === 0) {
                echo json_encode(["success" => false, "message" => "Selected class does not exist."]);
                exit;
            }

            // Check for existing enrollment
            $enrollCheck = $pdo->prepare("SELECT 1 FROM Enrollments WHERE StudentName = :sn AND ClassNum = :cn LIMIT 1");
            $enrollCheck->execute([':sn' => $studentName, ':cn' => $classNum]);
            if ($enrollCheck->rowCount() > 0) {
                echo json_encode(["success" => false, "message" => "Student is already enrolled in that class."]);
                exit;
            }

            // Insert new enrollment
            $insertEnroll = $pdo->prepare("INSERT INTO Enrollments (StudentName, ClassNum) VALUES (:sn, :cn)");
            if ($insertEnroll->execute([':sn' => $studentName, ':cn' => $classNum])) {
                echo json_encode(["success" => true, "message" => "Student enrolled in class successfully."]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to enroll student in the class."]);
            }
            break;

        case 'unenrollStudent':
            // === Remove student from a specific class ===
            $studentName = trim($_POST['studentName'] ?? '');
            $classNum = trim($_POST['classNum'] ?? '');

            if (!$studentName || !$classNum) {
                echo json_encode(["success" => false, "message" => "Missing student name or class number."]);
                exit;
            }

            // Check if student is currently enrolled in the class
            $enrollCheck = $pdo->prepare("SELECT 1 FROM Enrollments WHERE StudentName = :sn AND ClassNum = :cn");
            $enrollCheck->execute([':sn' => $studentName, ':cn' => $classNum]);
            if ($enrollCheck->rowCount() === 0) {
                echo json_encode(["success" => false, "message" => "Student is not enrolled in that class."]);
                exit;
            }

            // Delete enrollment record
            $delEnroll = $pdo->prepare("DELETE FROM Enrollments WHERE StudentName = :sn AND ClassNum = :cn");
            if ($delEnroll->execute([':sn' => $studentName, ':cn' => $classNum])) {
                echo json_encode(["success" => true, "message" => "Student unenrolled from class successfully."]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to unenroll student from class."]);
            }
            break;

        case 'deleteStudent':
            // === Completely delete a student and all their enrollments ===
            $studentName = trim($_POST['studentName'] ?? '');
            if (!$studentName) {
                echo json_encode(["success" => false, "message" => "Missing student name."]);
                exit;
            }

            // Confirm student exists
            $check = $pdo->prepare("SELECT 1 FROM Students WHERE StudentName = :sn LIMIT 1");
            $check->execute([':sn' => $studentName]);
            if ($check->rowCount() === 0) {
                echo json_encode(["success" => false, "message" => "Student not found."]);
                exit;
            }

            // First, delete all enrollments
            $delEnroll = $pdo->prepare("DELETE FROM Enrollments WHERE StudentName = :sn");
            $delEnroll->execute([':sn' => $studentName]);

            // Then, delete student record
            $stmt = $pdo->prepare("DELETE FROM Students WHERE StudentName = :sn");
            if ($stmt->execute([':sn' => $studentName])) {
                echo json_encode(["success" => true, "message" => "Student deleted successfully."]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to delete student."]);
            }
            break;

        case 'addClass':
            // === Add a new class to the system ===
            $className = trim($_POST['className'] ?? '');
            $classNum = trim($_POST['classNum'] ?? '');

            if (!$className || !$classNum) {
                echo json_encode(["success" => false, "message" => "Missing class name or class number."]);
                exit;
            }

            // Validate format: 3 capital letters + 3 digits (e.g., CSC101)
            if (!preg_match('/^[A-Z]{3}[0-9]{3}$/', $classNum)) {
                echo json_encode(["success" => false, "message" => "Invalid class number format. Expected format: XXX999 (e.g., CSC101)."]);
                exit;
            }

            // Check if class already exists
            $check = $pdo->prepare("SELECT 1 FROM Classes WHERE ClassNum = :cn LIMIT 1");
            $check->execute([':cn' => $classNum]);
            if ($check->rowCount() > 0) {
                echo json_encode(["success" => false, "message" => "Class number already exists."]);
                exit;
            }

            // Insert new class
            $stmt = $pdo->prepare("INSERT INTO Classes (ClassNum, ClassName) VALUES (:cn, :cname)");
            if ($stmt->execute([':cn' => $classNum, ':cname' => $className])) {
                echo json_encode(["success" => true, "message" => "Class added successfully."]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to add class."]);
            }
            break;

        case 'deleteClass':
            // === Delete a class and its related enrollments ===
            $classNum = trim($_POST['classNum'] ?? '');
            if (!$classNum) {
                echo json_encode(["success" => false, "message" => "Missing class number."]);
                exit;
            }

            // Check if class exists
            $check = $pdo->prepare("SELECT 1 FROM Classes WHERE ClassNum = :cn LIMIT 1");
            $check->execute([':cn' => $classNum]);
            if ($check->rowCount() === 0) {
                echo json_encode(["success" => false, "message" => "Class number not found."]);
                exit;
            }

            // Delete all enrollments tied to this class
            $delEnroll = $pdo->prepare("DELETE FROM Enrollments WHERE ClassNum = :cn");
            $delEnroll->execute([':cn' => $classNum]);

            // Delete class itself
            $stmt = $pdo->prepare("DELETE FROM Classes WHERE ClassNum = :cn");
            if ($stmt->execute([':cn' => $classNum])) {
                echo json_encode(["success" => true, "message" => "Class deleted successfully."]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to delete class."]);
            }
            break;

        default:
            // Fallback for unknown or missing action
            echo json_encode(["success" => false, "message" => "Invalid or missing 'action' parameter."]);
            break;
    }
} catch (PDOException $e) {
    // Handle database errors gracefully
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
