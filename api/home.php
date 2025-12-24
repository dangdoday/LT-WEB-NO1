<?php
session_start();

// Chặn truy cập trực tiếp
if (!isset($_SESSION['login_id'])) {
    header('Location: login.php');
    exit;
}

$login_id = $_SESSION['login_id'];
$login_time = $_SESSION['login_time'];

header('Content-Type: application/json');
echo json_encode([
    'login_id' => $login_id,
    'login_time' => $login_time
]);
