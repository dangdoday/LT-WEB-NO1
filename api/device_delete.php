<?php
require_once 'app/common/db.php';
require_once 'app/helpers/response.php';

// Nhận dữ liệu JSON từ Vue gửi lên
$input = json_decode(file_get_contents('php://input'), true);
$id = isset($input['id']) ? $input['id'] : null;

if (!$id) {
    jsonResponse(["status" => "error", "message" => "Thiếu ID thiết bị"], 400);
    exit;
}

try {
    $pdo = get_db_connection();

    // 1. Kiểm tra thiết bị có đang được mượn không
    $checkSql = "SELECT COUNT(*) FROM transactions WHERE device_id = :id AND returned_date IS NULL";
    $stmtCheck = $pdo->prepare($checkSql);
    $stmtCheck->execute([':id' => $id]);
    $isBorrowed = $stmtCheck->fetchColumn();

    if ($isBorrowed > 0) {
        jsonResponse(["status" => "error", "message" => "Không thể xóa: Thiết bị đang được mượn."], 400);
        exit;
    }

    // 2. Thực hiện xóa
    $deleteSql = "DELETE FROM devices WHERE id = :id";
    $stmt = $pdo->prepare($deleteSql);
    $stmt->execute([':id' => $id]);

    jsonResponse(["status" => "success", "message" => "Xóa thành công"]);

} catch (Exception $e) {
    jsonResponse(["status" => "error", "message" => "Lỗi server: " . $e->getMessage()], 500);
}