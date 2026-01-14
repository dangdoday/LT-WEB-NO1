<?php
error_reporting(0); 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/app/common/define.php';
require_once __DIR__ . '/app/common/db.php';
require_once __DIR__ . '/app/models/Transaction.php'; 

try {
    $deviceName = $_GET['device_name'] ?? '';
    $teacherId = $_GET['teacher_id'] ?? '';

    $data = Transaction::search($deviceName, $teacherId);

    echo json_encode([
        "status" => "success", 
        "data" => $data
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}