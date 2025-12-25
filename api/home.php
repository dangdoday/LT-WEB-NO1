<?php
session_start();

// Chặn truy cập trực tiếp

header('Content-Type: application/json');
if (!isset($_SESSION['login_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Chưa đăng nhập'
    ]);
    exit;
}

$login_id = $_SESSION['login_id'];
$login_time = $_SESSION['login_time'];

header('Content-Type: application/json');
echo json_encode([
    'login_id' => $login_id,
    'login_time' => $login_time
]);
