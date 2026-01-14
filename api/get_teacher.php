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
        // Trả về đường dẫn ảnh đầy đủ để frontend hiển thị.
        // DB có thể lưu dạng:
        // - 'web/image/avatar/<file>' (chuẩn)
        // - '<file>'
        // - '/api/web/image/avatar/<file>' (hiếm)
        $avatar = $teacher['avatar'] ?? '';
        if ($avatar === '') {
            $teacher['avatar_url'] = '';
        } elseif (preg_match('/^https?:\/\//', $avatar)) {
            $teacher['avatar_url'] = $avatar;
        } elseif (strpos($avatar, '/api/') === 0) {
            $teacher['avatar_url'] = $avatar;
        } elseif (strpos($avatar, 'web/') === 0) {
            $teacher['avatar_url'] = '/api/' . $avatar;
        } else {
            $teacher['avatar_url'] = '/api/web/image/avatar/' . $avatar;
        }

        jsonResponse(['status' => 'success', 'data' => $teacher]);
    } else {
        jsonResponse(['status' => 'error', 'message' => 'Không tìm thấy giáo viên'], 404);
    }
} catch (Exception $e) {
    jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
}
?>