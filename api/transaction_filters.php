<?php
error_reporting(0); 

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/app/common/define.php';
require_once __DIR__ . '/app/common/db.php';

try {
    $pdo = get_db_connection();
    
    $stmt = $pdo->query("SELECT id, name FROM teachers ORDER BY name ASC");
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        "status" => "success",
        "teachers" => $teachers
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}