<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

if (!defined('DB_PATH')) {
    define('DB_PATH', __DIR__ . '/storage/ltweb.sqlite');
}

require_once __DIR__ . '/app/common/db.php';

try {
    $pdo = get_db_connection();
    
    // Lấy danh sách giáo viên
    $stmt = $pdo->query("SELECT id, name FROM teachers ORDER BY name ASC");
    $teachers = $stmt->fetchAll();
    
    echo json_encode([
        "status" => "success",
        "teachers" => $teachers
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}