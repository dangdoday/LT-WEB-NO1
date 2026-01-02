<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
session_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/app/common/define.php';

$pdo = null;
try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

$errors = [];

function verifyRecaptcha($secret, $response)
{
    $url = 'https://www.google.com/recaptcha/api/siteverify';
    $payload = http_build_query([
        'secret' => $secret,
        'response' => $response,
    ]);

    // Uu tien dung cURL de tranh loi allow_url_fopen
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_TIMEOUT => 10,
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true) ?: [];
    }

    // Fallback file_get_contents (co the bi chan neu allow_url_fopen off)
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'content' => $payload,
            'timeout' => 10,
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ],
    ]);
    $result = @file_get_contents($url, false, $context);
    return json_decode($result, true) ?: [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_id = trim($_POST['login_id'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
    $recaptcha_secret = $CONFIG['RECAPTCHA_SECRET_KEY'] ?? '';
    $captcha_enabled = ($recaptcha_secret !== '');
    $is_local = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1', '::1'], true);

    if ($login_id === '') {
        $errors['login_id'] = 'Hay nhap login id';
    } elseif (strlen($login_id) < 4) {
        $errors['login_id'] = 'Hay nhap login id toi thieu 4 ky tu';
    }

    if ($password === '') {
        $errors['password'] = 'Hay nhap password';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Hay nhap password toi thieu 6 ky tu';
    }

    if ($captcha_enabled) {
        if (empty($recaptcha_response)) {
            $errors['login'] = 'Vui long xac nhan ban khong phai robot';
        } else {
            $captcha_success = verifyRecaptcha($recaptcha_secret, $recaptcha_response);
            if (empty($captcha_success['success'])) {
                // Cho phep bo qua loi recaptcha khi chay local (de thao tac nhanh tren dev)
                if (!$is_local) {
                    $errors['login'] = 'Xac thuc reCAPTCHA that bai';
                }
            }
        }
    }

    if (empty($errors)) {
        // Accept both plain-text and legacy MD5-hashed passwords to keep old accounts working
        $sql = "SELECT * FROM admins 
            WHERE login_id = :login_id 
              AND active_flag = 1
              AND (password = :password_plain OR password = :password_md5)
            LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':login_id' => $login_id,
            ':password_plain' => $password,
            ':password_md5' => md5($password),
        ]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin) {
            $errors['login'] = 'Login ID va password khong dung';
        } else {
            $_SESSION['login_id'] = $admin['login_id'];
            $_SESSION['login_time'] = date('Y-m-d H:i');
            echo json_encode([
                'status' => 'success',
                'login_id' => $admin['login_id'],
                'login_time' => $_SESSION['login_time'],
            ]);
            exit;
        }
    }

    echo json_encode(['status' => 'error', 'errors' => $errors]);
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
