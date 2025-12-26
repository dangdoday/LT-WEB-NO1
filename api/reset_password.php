<?php
require_once __DIR__ . '/app/common/define.php';
require_once __DIR__ . '/app/common/db.php';
require_once __DIR__ . '/app/helpers/response.php';
require_once __DIR__ . '/app/models/Admin.php';

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

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

$adminId = $input['admin_id'] ?? null;
$newPassword = $input['new_password'] ?? '';
$errors = [];

if ($newPassword === '') {
    $errors['new_password'] = 'Hay nhap mat khau moi';
} elseif (strlen($newPassword) < 6) {
    $errors['new_password'] = 'Hay nhap mat khau co toi thieu 6 ky tu';
}

if (empty($adminId)) {
    $errors['admin_id'] = 'Admin khong hop le';
}

if (!empty($errors)) {
    jsonResponse(['error' => 'Validation failed', 'fields' => $errors], 422);
    exit;
}

$admin = Admin::findById($adminId);
if (!$admin) {
    jsonResponse(['error' => 'Admin not found'], 404);
    exit;
}

if (empty($admin['reset_password_token'])) {
    jsonResponse(['error' => 'Reset token missing'], 400);
    exit;
}

Admin::resetPassword($adminId, $newPassword);
jsonResponse(['success' => true]);
