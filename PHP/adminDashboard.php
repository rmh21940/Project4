<?php
// PHP/adminDashboard.php

require_once __DIR__ . '/db.php';

$action = $_GET['action'] ?? '';

// If the action is to download a report, output a CSV file.
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
            $studentName = trim($_POST['studentName'] ?? '');
            $classNum = $_POST['studentClassNum'] ?? '';
            if (!$studentName || !$classNum) {
                echo json_encode(["success" => false, "message" => "Missing student name or class number."]);
                exit;
            }
            // Check if student already exists
            $check = $pdo->prepare("SELECT 1 FROM Students WHERE StudentName = :sn LIMIT 1");
            $check->execute([':sn' => $studentName]);
            if ($check->rowCount() === 0) {
                $stmt = $pdo->prepare("INSERT INTO Students (StudentName) VALUES (:sn)");
                $stmt->execute([':sn' => $studentName]);
            }
            // Enroll student in the specified class
            $classCheck = $pdo->prepare("SELECT 1 FROM Classes WHERE ClassNum = :cn LIMIT 1");
            $classCheck->execute([':cn' => $classNum]);
            if ($classCheck->rowCount() === 0) {
                echo json_encode(["success" => false, "message" => "Class number does not exist."]);
                exit;
            }
            $enrollCheck = $pdo->prepare("SELECT 1 FROM Enrollments WHERE StudentName = :sn AND ClassNum = :cn LIMIT 1");
            $enrollCheck->execute([':sn' => $studentName, ':cn' => $classNum]);
            if ($enrollCheck->rowCount() > 0) {
                echo json_encode(["success" => false, "message" => "Student is already enrolled in that class."]);
                exit;
            }
            $insertEnroll = $pdo->prepare("INSERT INTO Enrollments (StudentName, ClassNum) VALUES (:sn, :cn)");
            if ($insertEnroll->execute([':sn' => $studentName, ':cn' => $classNum])) {
                echo json_encode(["success" => true, "message" => "Student added and enrolled successfully."]);
            } else {
                echo json_encode(["success" => false, "message" => "Failed to enroll student in the class."]);
            }
            break;

        case 'deleteStudent':
            $studentName = trim($_POST['studentName'] ?? '');
            $classNum = $_POST['studentClassNum'] ?? '';
            if (!$studentName || !$classNum) {
                echo json_encode(["success" => false, "message" => "Missing student name or class number."]);
                exit;
            }
            $enrollCheck = $pdo->prepare("SELECT 1 FROM Enrollments WHERE StudentName = :sn AND ClassNum = :cn");
            $enrollCheck->execute([':sn' => $studentName, ':cn' => $classNum]);
            if ($enrollCheck->rowCount() === 0) {
                echo json_encode(["success" => false, "message" => "No enrollment found for that student/class."]);
                exit;
            }
            $delEnroll = $pdo->prepare("DELETE FROM Enrollments WHERE StudentName = :sn AND ClassNum = :cn");
            $delEnroll->execute([':sn' => $studentName, ':cn' => $classNum]);
            $stillEnrolled = $pdo->prepare("SELECT 1 FROM Enrollments WHERE StudentName = :sn LIMIT 1");
            $stillEnrolled->execute([':sn' => $studentName]);
            if ($stillEnrolled->rowCount() === 0) {
                $stmt = $pdo->prepare("DELETE FROM Students WHERE StudentName = :sn");
                $stmt->execute([':sn' => $studentName]);
            }
            echo json_encode(["success" => true, "message" => "Student unenrolled/deleted successfully."]);
            break;

        case 'addClass':
            $classNum = $_POST['classNum'] ?? '';
            $className = trim($_POST['className'] ?? '');
            if (!$classNum || !$className) {
                echo json_encode(["success" => false, "message" => "Missing class number or class name."]);
                exit;
            }
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
            $classNum = $_POST['classNum'] ?? '';
            if (!$classNum) {
                echo json_encode(["success" => false, "message" => "Missing class number."]);
                exit;
            }
            $check = $pdo->prepare("SELECT 1 FROM Classes WHERE ClassNum = :cn LIMIT 1");
            $check->execute([':cn' => $classNum]);
            if ($check->rowCount() === 0) {
                echo json_encode(["success" => false, "message" => "Class number not found."]);
                exit;
            }
            $delEnroll = $pdo->prepare("DELETE FROM Enrollments WHERE ClassNum = :cn");
            $delEnroll->execute([':cn' => $classNum]);
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
