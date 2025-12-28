<?php
// Cấu hình CORS
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: GET");

require_once 'app/common/db.php';
require_once 'app/helpers/response.php';

$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id) {
    jsonResponse(['status' => 'error', 'message' => 'Thiếu ID thiết bị'], 400);
    exit;
}

try {
    $pdo = get_db_connection();
    // Giả sử bảng devices có các cột: id, name, serial, description, image
    $stmt = $pdo->prepare("SELECT * FROM devices WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    $device = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($device) {
        // Xử lý đường dẫn ảnh (nếu thiết bị có ảnh)
        if (!empty($device['image'])) {
            $device['image_url'] = '/api/web/image/device/' . $device['image'];
        } else {
            $device['image_url'] = '';
        }

        jsonResponse(['status' => 'success', 'data' => $device]);
    } else {
        jsonResponse(['status' => 'error', 'message' => 'Không tìm thấy thiết bị'], 404);
    }
} catch (Exception $e) {
    jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
}
?>