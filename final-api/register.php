<?php
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_id = trim($_POST['login_id'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Validate login_id
    if ($login_id === '') {
        $errors['login_id'] = 'Hãy nhập login id';
    } elseif (strlen($login_id) < 4) {
        $errors['login_id'] = 'Login id tối thiểu 4 ký tự';
    }

    // Validate password
    if ($password === '') {
        $errors['password'] = 'Hãy nhập password';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password tối thiểu 6 ký tự';
    }

    // Validate confirm password
    if ($confirm_password === '') {
        $errors['confirm_password'] = 'Hãy nhập lại password';
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Password nhập lại không khớp';
    }

    // Check login_id exists
    if (empty($errors)) {
        if (isset($pdo)) {
            $stmt = $pdo->prepare(
                "SELECT id FROM admins WHERE login_id = :login_id LIMIT 1"
            );
            $stmt->execute([':login_id' => $login_id]);

            if ($stmt->fetch()) {
                $errors['login_id'] = 'Login id đã tồn tại';
            }
        } else {
            $errors['system'] = 'Lỗi kết nối CSDL';
        }
    }

    // Insert admin
    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "INSERT INTO admins (login_id, password, actived_flag)
             VALUES (:login_id, SHA2(:password, 256), 1)"
        );
        $stmt->execute([
            ':login_id' => $login_id,
            ':password' => $password
        ]);

        header('Location: login.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký tài khoản</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

        /* ----------------------------------------------------------- */
        /* FIX LỖI 2 CON MẮT: Ẩn icon mặc định của trình duyệt */
        /* ----------------------------------------------------------- */
        input::-ms-reveal,
        input::-ms-clear {
            display: none;
        }
        input::-webkit-text-password-reveal-button {
            display: none;
            -webkit-appearance: none;
        }
        /* ----------------------------------------------------------- */

        .register-container {
            width: 450px;
            border: 2px solid #4a7ebb; 
            padding: 40px 30px;
            background-color: white;
            box-sizing: border-box;
        }

        h2 {
            text-align: center;
            color: #4a7ebb;
            margin-top: 0;
            margin-bottom: 30px;
            text-transform: uppercase;
            font-size: 20px;
        }

        .form-group {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            width: 140px;
            font-size: 16px;
            color: #000;
        }

        .input-wrapper {
            flex: 1;
            position: relative;
            display: flex; /* Dùng flex để giữ input full width */
        }

        .input-wrapper input {
            width: 100%;
            padding: 8px 35px 8px 8px; /* Padding phải chừa chỗ cho icon */
            border: 1px solid #7f9db9;
            background-color: #e8f0fe;
            font-size: 14px;
            outline: none;
            height: 35px;
            box-sizing: border-box;
        }

        .input-wrapper input:focus {
            border-color: #4a7ebb;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%; /* Căn giữa theo chiều dọc */
            transform: translateY(-50%); /* Căn giữa chính xác */
            cursor: pointer;
            color: #7f9db9;
            font-size: 14px;
            z-index: 10; /* Đảm bảo icon nổi lên trên input */
        }
        
        .toggle-password:hover {
            color: #4a7ebb;
        }

        .btn-container {
            display: flex;
            justify-content: center;
            padding-left: 70px;
            margin-top: 10px;
        }

        .btn-submit {
            background-color: #547ebc;
            color: white;
            border: none;
            padding: 10px 35px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-submit:hover {
            background-color: #416aa6;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            font-style: italic;
            text-decoration: underline;
            color: #000;
            font-size: 14px;
        }

        .error-msg {
            color: red;
            font-size: 13px;
            margin-left: 140px;
            margin-top: -20px;
            margin-bottom: 15px;
            display: block;
        }
        
        .system-error {
            color: red;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <div class="register-container">
        <h2>Đăng Ký</h2>

        <?php if (!empty($errors['system'])): ?>
            <div class="system-error"><?= $errors['system'] ?></div>
        <?php endif; ?>

        <form method="post">
            
            <div class="form-group">
                <label>Login ID</label>
                <div class="input-wrapper">
                    <input type="text" name="login_id" value="<?= htmlspecialchars($login_id) ?>">
                </div>
            </div>
            <?php if (!empty($errors['login_id'])): ?>
                <span class="error-msg"><?= $errors['login_id'] ?></span>
            <?php endif; ?>

            <div class="form-group">
                <label>Password</label>
                <div class="input-wrapper">
                    <input type="password" name="password" id="password">
                    <i class="fa-solid fa-eye toggle-password" onclick="togglePass('password', this)"></i>
                </div>
            </div>
            <?php if (!empty($errors['password'])): ?>
                <span class="error-msg"><?= $errors['password'] ?></span>
            <?php endif; ?>

            <div class="form-group">
                <label>Nhập lại Pass</label>
                <div class="input-wrapper">
                    <input type="password" name="confirm_password" id="confirm_password">
                    <i class="fa-solid fa-eye toggle-password" onclick="togglePass('confirm_password', this)"></i>
                </div>
            </div>
            <?php if (!empty($errors['confirm_password'])): ?>
                <span class="error-msg"><?= $errors['confirm_password'] ?></span>
            <?php endif; ?>

            <div class="btn-container">
                <button type="submit" class="btn-submit">Đăng ký</button>
            </div>

        </form>

        <a href="login.php" class="back-link">← Quay lại đăng nhập</a>
    </div>

    <script>
        function togglePass(inputId, icon) {
            const input = document.getElementById(inputId);
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>

</body>
</html>