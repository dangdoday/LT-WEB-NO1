
<?php
date_default_timezone_set('Asia/Ho_Chi_Minh');
session_start();
header('Content-Type: application/json; charset=utf-8');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

require_once __DIR__ . '/app/common/define.php';

$pdo = null;
try {
    $pdo = new PDO("sqlite:" . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_id = trim($_POST['login_id'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';

    if ($login_id === '') {
        $errors['login_id'] = 'Hãy nhập login id';
    } elseif (strlen($login_id) < 4) {
        $errors['login_id'] = 'Hãy nhập login id tối thiểu 4 ký tự';
    }

    if ($password === '') {
        $errors['password'] = 'Hãy nhập password';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Hãy nhập password tối thiểu 6 ký tự';
    }

    if (empty($recaptcha_response)) {
        $errors['login'] = 'Vui lòng xác nhận bạn không phải robot';
    } else {
        $recaptcha_secret = $CONFIG['RECAPTCHA_SECRET_KEY'] ?? '';
        $verify = file_get_contents(
            "https://www.google.com/recaptcha/api/siteverify?secret=$recaptcha_secret&response=$recaptcha_response"
        );
        $captcha_success = json_decode($verify, true);
        if (!$captcha_success['success']) {
            $errors['login'] = 'Xác thực reCAPTCHA thất bại';
        }
    }

    if (empty($errors)) {
        $password_hash = hash('sha256', $password);
        $sql = "SELECT * FROM admins 
                WHERE login_id = :login_id 
                  AND password = :password_hash
                  AND active_flag = 1
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':login_id' => $login_id,
            ':password_hash' => $password_hash
        ]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin) {
            $errors['login'] = 'Login ID và password không đúng';
        } else {
            $_SESSION['login_id'] = $admin['login_id'];
            $_SESSION['login_time'] = date('Y-m-d H:i');
            echo json_encode([
                'status' => 'success', 
                'login_id' => $admin['login_id'], 
                'login_time' => $_SESSION['login_time']
            ]);
            exit;
        }
    }
    
    echo json_encode(['status' => 'error', 'errors' => $errors]);
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
?>