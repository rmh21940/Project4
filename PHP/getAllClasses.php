<?php
// PHP/getAllClasses.php
require_once __DIR__ . '/db.php';
header('Content-Type: application/json');

$stmt = $pdo->query("SELECT ClassNum, ClassName FROM Classes ORDER BY ClassName ASC");
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(["success" => true, "classes" => $classes]);
exit;
