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

$id = $_POST['id'] ?? null;
$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');
$errors = [];

if (!$id) {
    $errors['id'] = 'Thiếu id thiết bị.';
}

if ($name === '') {
    $errors['name'] = 'Hãy nhập tên thiết bị.';
}

if ($description === '') {
    $errors['description'] = 'Hãy nhập mô tả chi tiết.';
} elseif (strlen($description) > 1000) {
    $errors['description'] = 'Không nhập quá 1000 ký tự';
}

if (!empty($errors)) {
    jsonResponse(['error' => 'Validation failed', 'fields' => $errors], 422);
    exit;
}

$pdo = get_db_connection();
$stmt = $pdo->prepare('SELECT * FROM devices WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $id]);
$current = $stmt->fetch();
if (!$current) {
    jsonResponse(['error' => 'Not found'], 404);
    exit;
}

$avatarFilename = $current['avatar'];

// Handle optional avatar upload
if (!empty($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
    $avatar = $_FILES['avatar'];
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    if (function_exists('mime_content_type')) {
        $mime = mime_content_type($avatar['tmp_name']);
    } elseif (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $avatar['tmp_name']) : false;
        if ($finfo) finfo_close($finfo);
    } else {
        $info = getimagesize($avatar['tmp_name']);
        $mime = $info['mime'] ?? false;
    }

    if (!isset($allowed[$mime])) {
        jsonResponse(['error' => 'Invalid file type', 'fields' => ['avatarFile' => 'Avatar không hợp lệ.']], 422);
        exit;
    }

    $uploadDir = __DIR__ . '/web/avatar';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $newName = 'device_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
    $target = $uploadDir . '/' . $newName;
    if (!move_uploaded_file($avatar['tmp_name'], $target)) {
        jsonResponse(['error' => 'Upload failed'], 500);
        exit;
    }

    $avatarFilename = $newName;
}

try {
    $stmt = $pdo->prepare('UPDATE devices SET name = :name, description = :description, avatar = :avatar WHERE id = :id');
    $stmt->execute([
        'name' => $name,
        'description' => $description,
        'avatar' => $avatarFilename,
        'id' => $id,
    ]);

    jsonResponse(['status' => 'success', 'id' => $id, 'avatar' => $avatarFilename]);
} catch (Throwable $e) {
    jsonResponse(['error' => 'Database error'], 500);
}
