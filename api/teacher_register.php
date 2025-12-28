<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}
require_once 'app/common/db.php';
require_once 'app/helpers/response.php';

// Nhận dữ liệu
$id = $_POST['id'] ?? null;
$name = $_POST['name'] ?? '';
$specialized = $_POST['specialized'] ?? '';
$degree = $_POST['degree'] ?? '';
$description = $_POST['description'] ?? '';

// Validate cơ bản (Backend cũng phải validate)
if (empty($name) || empty($specialized) || empty($degree)) {
    jsonResponse(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ'], 400);
    exit;
}

try {
    $pdo = get_db_connection();

    // Xử lý upload ảnh
    $avatarName = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/web/image/avatar/';
        // Tạo tên file ngẫu nhiên để tránh trùng
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $avatarName = uniqid() . '.' . $ext;

        if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $avatarName)) {
            throw new Exception("Lỗi khi lưu ảnh.");
        }
    }

    if ($id) {
        // --- LOGIC UPDATE ---
        $sql = "UPDATE teachers SET name=?, specialized=?, degree=?, description=?";
        $params = [$name, $specialized, $degree, $description];

        // Chỉ update cột avatar nếu người dùng có up ảnh mới
        if ($avatarName) {
            $sql .= ", avatar=?";
            $params[] = $avatarName;
        }

        $sql .= " WHERE id=?";
        $params[] = $id;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        jsonResponse(['status' => 'success', 'message' => 'Cập nhật thành công']);

    } else {
        // --- LOGIC INSERT (Tạo mới) ---
        if (!$avatarName) {
            jsonResponse(['status' => 'error', 'message' => 'Vui lòng chọn ảnh'], 400);
            exit;
        }

        $sql = "INSERT INTO teachers (name, specialized, degree, description, avatar) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $specialized, $degree, $description, $avatarName]);

        jsonResponse(['status' => 'success', 'message' => 'Đăng ký thành công']);
    }

} catch (Exception $e) {
    jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
}
?>