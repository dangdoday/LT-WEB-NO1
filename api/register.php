
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
session_start();

// Always return JSON on error
set_exception_handler(function($e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error',
        'details' => $e->getMessage()
    ]);
    exit;
});

try {
    $db_path = __DIR__ . '/../app/common/db.php';
    if (file_exists($db_path)) {
        require_once $db_path;
    } else {
        require_once __DIR__ . '/app/common/db.php';
    }

    $pdo = get_db_connection();

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
                "INSERT INTO admins (login_id, password, active_flag)
                 VALUES (:login_id, :password, 1)"
            );
            $stmt->execute([
                ':login_id' => $login_id,
                ':password' => $password
            ]);
            echo json_encode(['status' => 'success']);
            exit;
        }
        echo json_encode(['status' => 'error', 'errors' => $errors]);
        exit;
    }
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error',
        'details' => $e->getMessage()
    ]);
    exit;
}
