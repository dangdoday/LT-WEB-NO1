<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Xóa toàn bộ session
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'message' => 'Đã logout']);
    exit;
}
http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
exit;
