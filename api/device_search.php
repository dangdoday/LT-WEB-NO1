<?php
require_once 'app/common/db.php';
require_once 'app/helpers/response.php';

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

try {
    $pdo = get_db_connection();

    // Dùng subquery để đếm số lượng giao dịch chưa trả của từng thiết bị
    // is_borrowed sẽ là con số: 0 (Rảnh), 1, 2... (Đang mượn)
    $sql = "SELECT * FROM (
                SELECT d.id, d.name, d.serial,
                (SELECT COUNT(*) FROM transactions t 
                 WHERE t.device_id = d.id AND t.returned_date IS NULL) as is_borrowed
                FROM devices d
                WHERE 1=1
    ";

    $params = [];

    // 1. Tìm theo tên
    if (!empty($keyword)) {
        $sql .= " AND (d.name LIKE :keyword)";
        $params[':keyword'] = "%$keyword%";
    }

    $sql .= ") as temp_table WHERE 1=1";

    // 2. Tìm theo trạng thái (SỬA LẠI ĐOẠN NÀY CHO CHUẨN)
    if ($status !== '') {
        if ($status == '1') {
            // Nếu chọn "Đang mượn": Lấy tất cả thiết bị có số lần mượn lớn hơn 0
            $sql .= " AND is_borrowed > 0";
        } else {
            // Nếu chọn "Đang rảnh": Lấy tất cả thiết bị có số lần mượn bằng 0
            $sql .= " AND is_borrowed = 0";
        }
    }

    $sql .= " ORDER BY id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);

    jsonResponse([
        "status" => "success",
        "data" => $devices
    ]);

} catch (Exception $e) {
    jsonResponse([
        "status" => "error",
        "message" => "Lỗi SQL: " . $e->getMessage()
    ], 500);
}
?>