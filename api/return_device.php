<?php
require_once __DIR__ . '/app/common/define.php';
require_once __DIR__ . '/app/common/db.php';
require_once __DIR__ . '/app/helpers/response.php';
require_once __DIR__ . '/app/models/Device.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    $input = $_POST;
}

$deviceId = $input['device_id'] ?? '';
$errors = [];

if ($deviceId === '') {
    $errors['device_id'] = 'Hay chon thiet bi.';
} elseif (!Device::findById($deviceId)) {
    $errors['device_id'] = 'Thiet bi khong ton tai.';
}

if (!empty($errors)) {
    jsonResponse(['error' => 'Validation failed', 'fields' => $errors], 422);
    exit;
}

try {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare(
        "UPDATE transactions
         SET returned_date = CURRENT_TIMESTAMP
         WHERE id = (
             SELECT id FROM transactions
             WHERE device_id = :device_id
               AND (returned_date IS NULL OR returned_date = '')
             ORDER BY id DESC
             LIMIT 1
         )"
    );
    $stmt->execute(['device_id' => $deviceId]);

    if ($stmt->rowCount() === 0) {
        jsonResponse(['error' => 'Khong tim thay giao dich dang muon.'], 404);
        exit;
    }

    jsonResponse(['success' => true]);
} catch (Throwable $e) {
    jsonResponse(['error' => 'Server error'], 500);
}


