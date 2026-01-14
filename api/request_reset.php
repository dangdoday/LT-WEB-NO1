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

$loginId = trim($input['login_id'] ?? '');
$errors = [];

if ($loginId === '') {
    $errors['login_id'] = 'Hay nhap login id';
} elseif (strlen($loginId) < 4) {
    $errors['login_id'] = 'Hay nhap login id toi thieu 4 ky tu';
}

$admin = null;
if (empty($errors)) {
    $admin = Admin::findByLoginId($loginId);
    if (!$admin) {
        $errors['login_id'] = 'Login id khong ton tai trong he thong';
    }
}

if (!empty($errors)) {
    jsonResponse(['error' => 'Validation failed', 'fields' => $errors], 422);
    exit;
}

$token = (string) microtime(true);
Admin::updateResetToken($admin['id'], $token);

jsonResponse(['success' => true, 'reset_password_token' => $token]);
