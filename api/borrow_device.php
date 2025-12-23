<?php
require_once __DIR__ . '/app/common/define.php';
require_once __DIR__ . '/app/common/db.php';
require_once __DIR__ . '/app/helpers/response.php';
require_once __DIR__ . '/app/models/Device.php';
require_once __DIR__ . '/app/models/Teacher.php';
require_once __DIR__ . '/app/models/Classroom.php';
require_once __DIR__ . '/app/models/Transaction.php';

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
$teacherId = $input['teacher_id'] ?? '';
$classroomId = $input['classroom_id'] ?? '';
$comment = trim($input['comment'] ?? '');
$startPlan = $input['start_transaction_plan'] ?? '';
$endPlan = $input['end_transaction_plan'] ?? '';

$errors = [];

if ($deviceId === '') {
    $errors['device_id'] = 'Hay chon thiet bi.';
} elseif (!Device::findById($deviceId)) {
    $errors['device_id'] = 'Thiet bi khong ton tai.';
}

if ($teacherId === '') {
    $errors['teacher_id'] = 'Hay chon giao vien.';
} elseif (!Teacher::findById($teacherId)) {
    $errors['teacher_id'] = 'Giao vien khong ton tai.';
}

if ($classroomId === '') {
    $errors['classroom_id'] = 'Hay chon lop hoc.';
} elseif (!Classroom::findById($classroomId)) {
    $errors['classroom_id'] = 'Lop hoc khong ton tai.';
}

if ($startPlan === '') {
    $errors['start_transaction_plan'] = 'Hay chon thoi gian bat dau.';
}

if ($endPlan === '') {
    $errors['end_transaction_plan'] = 'Hay chon thoi gian ket thuc.';
}

if (!empty($errors)) {
    jsonResponse(['error' => 'Validation failed', 'fields' => $errors], 422);
    exit;
}

try {
    $transactionId = Transaction::create([
        'device_id' => $deviceId,
        'teacher_id' => $teacherId,
        'classroom_id' => $classroomId,
        'comment' => $comment,
        'start_transaction_plan' => $startPlan,
        'end_transaction_plan' => $endPlan,
        'type' => 'borrow',
    ]);

    jsonResponse(['success' => true, 'transaction_id' => $transactionId]);
} catch (Throwable $e) {
    jsonResponse(['error' => 'Server error'], 500);
}
