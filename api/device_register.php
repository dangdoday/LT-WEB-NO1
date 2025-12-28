<?php
require_once __DIR__ . '/app/common/define.php';
require_once __DIR__ . '/app/common/db.php';
require_once __DIR__ . '/app/helpers/response.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
    exit;
}

// 1. Nhận ID (nếu có)
$id = $_POST['id'] ?? null;
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$errors = [];

// Validate Tên
if ($name === '') {
    $errors['name'] = 'Hay nhap ten thiet bi.';
}

// Validate Mô tả
if ($description === '') {
    $errors['description'] = 'Hay nhap mo ta chi tiet.';
} elseif (strlen($description) > 1000) {
    $errors['description'] = 'Khong nhap qua 1000 ky tu';
}

// 2. Kiểm tra có file ảnh mới được upload không
$hasNewAvatar = !empty($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK;

// Validate Avatar:
// - Nếu KHÔNG có ID (Thêm mới) -> Bắt buộc phải có ảnh.
// - Nếu CÓ ID (Sửa) -> Không bắt buộc.
if (!$id && !$hasNewAvatar) {
    $errors['avatarFile'] = 'Hay chon avatar';
}

if (!empty($errors)) {
    jsonResponse(['error' => 'Validation failed', 'fields' => $errors], 422);
    exit;
}

// 3. Xử lý upload ảnh (Chỉ chạy nếu có ảnh mới)
$filename = null;

if ($hasNewAvatar) {
    $avatar = $_FILES['avatar'];
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    // Kiểm tra MIME type
    if (function_exists('mime_content_type')) {
        $mime = mime_content_type($avatar['tmp_name']);
    } elseif (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $avatar['tmp_name']) : false;
        if ($finfo) {
            finfo_close($finfo);
        }
    } else {
        $imageInfo = getimagesize($avatar['tmp_name']);
        $mime = $imageInfo['mime'] ?? false;
    }

    if (!isset($allowed[$mime])) {
        jsonResponse(['error' => 'Invalid file type', 'fields' => ['avatarFile' => 'Avatar khong hop le.']], 422);
        exit;
    }

    // Lưu ảnh
    $uploadDir = __DIR__ . '/web/avatar'; // Lưu ý: Code cũ bạn dùng folder này
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = 'device_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
    $targetPath = $uploadDir . '/' . $filename;

    if (!move_uploaded_file($avatar['tmp_name'], $targetPath)) {
        jsonResponse(['error' => 'Upload failed'], 500);
        exit;
    }
}

// 4. Xử lý Database (Chia 2 trường hợp INSERT và UPDATE)
try {
    $pdo = get_db_connection();

    if ($id) {
        // --- TRƯỜNG HỢP SỬA (UPDATE) ---
        $sql = "UPDATE devices SET name = :name, description = :description";
        $params = [
            'name' => $name,
            'description' => $description,
            'id' => $id
        ];

        // Chỉ update cột avatar nếu có upload ảnh mới
        if ($filename) {
            $sql .= ", avatar = :avatar";
            $params['avatar'] = $filename;
        }

        $sql .= " WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        jsonResponse(['success' => true, 'message' => 'Cập nhật thành công']);
    } else {
        // --- TRƯỜNG HỢP THÊM MỚI (INSERT) ---
        // Lúc này $filename chắc chắn có dữ liệu vì đã validate ở trên
        $stmt = $pdo->prepare(
            'INSERT INTO devices (name, description, avatar) VALUES (:name, :description, :avatar)'
        );
        $stmt->execute([
            'name' => $name,
            'description' => $description,
            'avatar' => $filename,
        ]);

        jsonResponse(['success' => true, 'id' => $pdo->lastInsertId(), 'avatar' => $filename]);
    }

} catch (Throwable $e) {
    jsonResponse(['error' => 'Database error: ' . $e->getMessage()], 500);
}
?>