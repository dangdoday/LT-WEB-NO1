<?php
session_start();

// -----------------------------------------------------------------------
// CẤU HÌNH ĐƯỜNG DẪN DB
// -----------------------------------------------------------------------
$db_path = __DIR__ . '/../app/common/db.php';

if (file_exists($db_path)) {
    require_once $db_path;
} else {
    require_once __DIR__ . '/app/common/db.php'; 
}

$errors = [];
$login_id = '';

$config = [];
if (file_exists(__DIR__ . '/config_env.php')) {
    $config = require __DIR__ . '/config_env.php';
} else {
    // Fallback hoặc báo lỗi nếu thiếu file môi trường
    die('Thiếu file cấu hình môi trường!');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_id = trim($_POST['login_id'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validate login_id
    if ($login_id === '') {
        $errors['login_id'] = 'Hãy nhập login id';
    } elseif (strlen($login_id) < 4) {
        $errors['login_id'] = 'Hãy nhập login id tối thiểu 4 ký tự';
    }

    // Validate password
    if ($password === '') {
        $errors['password'] = 'Hãy nhập password';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Hãy nhập password tối thiểu 6 ký tự';
    }

    // Validate reCAPTCHA
    if (empty($_POST['g-recaptcha-response'])) {
        $errors['login'] = 'Vui lòng xác nhận bạn không phải robot';
    } else {
        // Lưu ý: Đây là Secret Key (Server side)
        $recaptcha_secret = $config['RECAPTCHA_SECRET_KEY'] ?? '';
        $recaptcha_response = $_POST['g-recaptcha-response'];

        $verify = file_get_contents(
            "https://www.google.com/recaptcha/api/siteverify?secret=$recaptcha_secret&response=$recaptcha_response"
        );
        $captcha_success = json_decode($verify, true);

        if (!$captcha_success['success']) {
            $errors['login'] = 'Xác thực reCAPTCHA thất bại';
        }
    }

    // Check DB
    if (empty($errors)) {
        if (isset($pdo)) {
            $sql = "SELECT * FROM admins 
                    WHERE login_id = :login_id 
                      AND password = SHA2(:password, 256)
                      AND actived_flag = 1
                    LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':login_id' => $login_id,
                ':password' => $password
            ]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$admin) {
                $errors['login'] = 'Login ID và password không đúng';
            } else {
                // Login success
                $_SESSION['login_id'] = $admin['login_id'];
                $_SESSION['login_time'] = date('Y-m-d H:i');

                header('Location: home.php');
                exit;
            }
        } else {
            $errors['login'] = 'Lỗi kết nối CSDL ($pdo không tồn tại). Kiểm tra file db.php';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <title>Đăng nhập Hệ thống</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #fff;
        }

        /* Khung bao ngoài */
        .login-container {
            width: 500px; /* TĂNG WIDTH TỪ 420px -> 500px ĐỂ CHỨA VỪA CAPTCHA */
            border: 2px solid #4a7ebb; 
            padding: 40px 30px;
            background-color: white;
            box-sizing: border-box;
        }

        /* Dòng nhập liệu */
        .form-group {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            width: 110px; 
            font-size: 16px;
            color: #000;
            font-weight: normal;
        }

        .form-group input {
            flex: 1;
            padding: 8px;
            border: 1px solid #7f9db9;
            background-color: #e8f0fe;
            font-size: 14px;
            outline: none;
        }

        .form-group input:focus {
            border-color: #4a7ebb;
        }

        /* Link Quên password / Đăng ký */
        .link-group {
            text-align: center;
            margin-bottom: 20px;
            padding-left: 110px; /* Căn thẳng hàng với input */
            /* Hoặc nếu muốn căn giữa khung thì bỏ padding-left đi */
        }

        .link-group a {
            font-style: italic;
            text-decoration: underline;
            color: #000;
            font-size: 15px;
            margin: 0 5px;
        }

        /* Khu vực Captcha */
        .captcha-container {
            margin-left: 110px; /* Căn thẳng hàng với label bên trên */
            margin-bottom: 20px;
        }

        /* Nút Đăng nhập */
        .btn-container {
            display: flex;
            justify-content: center;
            padding-left: 110px; /* Căn giữa so với phần input (bù trừ label) */
        }

        .btn-submit {
            background-color: #547ebc;
            color: white;
            border: none;
            padding: 10px 40px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-submit:hover {
            background-color: #416aa6;
        }

        /* Phần hiển thị lỗi */
        .error-msg {
            color: red;
            font-size: 13px;
            margin-left: 110px;
            margin-top: -20px;
            margin-bottom: 15px;
            display: block;
        }

        .main-error {
            color: red;
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <?php if (!empty($errors['login'])): ?>
            <div class="main-error"><?= $errors['login'] ?></div>
        <?php endif; ?>

        <form method="post">
            <div class="form-group">
                <label>Người dùng</label>
                <input type="text" name="login_id" value="<?= htmlspecialchars($login_id) ?>">
            </div>
            <?php if (!empty($errors['login_id'])): ?>
                <span class="error-msg"><?= $errors['login_id'] ?></span>
            <?php endif; ?>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password">
            </div>
            <?php if (!empty($errors['password'])): ?>
                <span class="error-msg"><?= $errors['password'] ?></span>
            <?php endif; ?>

            <div class="link-group">
                <a href="register.php">Đăng ký</a> | <a href="#">Quên password</a>
            </div>
            
            <div class="captcha-container">
                <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($config['RECAPTCHA_SITE_KEY'] ?? '') ?>"></div>
            </div>

            <div class="btn-container">
                <button type="submit" class="btn-submit">Đăng nhập</button>
            </div>
        </form>
    </div>

</body>
</html>