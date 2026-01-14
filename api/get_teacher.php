<?php
require_once 'app/common/db.php';
require_once 'app/helpers/response.php';

$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id) {
    jsonResponse(['status' => 'error', 'message' => 'Thiếu ID'], 400);
    exit;
}

try {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare("SELECT * FROM teachers WHERE id = :id LIMIT 1");
    $stmt->execute(['id' => $id]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($teacher) {
        // Trả về đường dẫn ảnh đầy đủ để frontend hiển thị
        // Giả sử ảnh bạn lưu trong folder: api/web/image/avatar/
        // Bạn cần chỉnh lại đường dẫn này cho đúng cấu trúc folder thực tế của bạn
        $teacher['avatar_url'] = '/api/web/image/avatar/' . $teacher['avatar'];

        jsonResponse(['status' => 'success', 'data' => $teacher]);
    } else {
        jsonResponse(['status' => 'error', 'message' => 'Không tìm thấy giáo viên'], 404);
    }
} catch (Exception $e) {
    jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
}
?>