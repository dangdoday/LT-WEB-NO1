<?php
require_once __DIR__ . '/app/common/define.php';
require_once __DIR__ . '/app/common/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$specialized = trim($_POST['specialized'] ?? '');
$degree = trim($_POST['degree'] ?? '');
$description = trim($_POST['description'] ?? '');

$errors = [];
if ($name === '') {
    $errors['name'] = 'Hay nhap ten giao vien.';
} elseif (strlen($name) > 100) {
    $errors['name'] = 'Khong nhap qua 100 ky tu.';
}

if ($specialized === '') {
    $errors['specialized'] = 'Hay chon chuyen nganh.';
}

if ($degree === '') {
    $errors['degree'] = 'Hay chon bang cap.';
}

if ($description === '') {
    $errors['description'] = 'Hay nhap mo ta chi tiet.';
} elseif (strlen($description) > 1000) {
    $errors['description'] = 'Khong nhap qua 1000 ky tu.';
}

if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    $errors['avatarFile'] = 'Hay chon avatar.';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['error' => 'Validation failed', 'fields' => $errors]);
    exit;
}

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
    if ($finfo) {
        finfo_close($finfo);
    }
} else {
    $imageInfo = getimagesize($avatar['tmp_name']);
    $mime = $imageInfo['mime'] ?? false;
}

if (!isset($allowed[$mime])) {
    http_response_code(422);
    echo json_encode(['error' => 'Invalid file type', 'fields' => ['avatarFile' => 'Avatar khong hop le.']]);
    exit;
}

$uploadDir = __DIR__ . '/web/image/avatar';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$filename = 'teacher_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
$targetPath = $uploadDir . '/' . $filename;

if (!move_uploaded_file($avatar['tmp_name'], $targetPath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Upload failed']);
    exit;
}

try {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare(
        'INSERT INTO teachers (name, specialized, degree, description, avatar, created) VALUES (:name, :specialized, :degree, :description, :avatar, CURRENT_TIMESTAMP)'
    );
    $stmt->execute([
        'name' => $name,
        'specialized' => $specialized,
        'degree' => $degree,
        'description' => $description,
        'avatar' => $filename,
    ]);

    echo json_encode(['success' => true, 'id' => $pdo->lastInsertId(), 'avatar' => $filename]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
