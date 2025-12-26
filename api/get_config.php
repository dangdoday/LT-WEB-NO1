<?php
// api/get_config.php
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *"); // Cho phép Vue gọi sang

// 1. Load file define để lấy biến $CONFIG
require_once __DIR__ . '/app/common/define.php';

// 2. Chỉ trả về Site Key cho Frontend
echo json_encode([
    'status' => 'success',
 'site_key' => $CONFIG['RECAPTCHA_SITE_KEY'] ?? ''
]);