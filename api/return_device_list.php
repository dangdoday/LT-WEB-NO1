<?php
require_once __DIR__ . '/app/common/define.php';
require_once __DIR__ . '/app/common/db.php';
require_once __DIR__ . '/app/helpers/response.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
    exit;
}

$deviceName = $_GET['device_name'] ?? '';
$teacherId = $_GET['teacher_id'] ?? '';
$classroomId = $_GET['classroom_id'] ?? '';

try {
    $pdo = get_db_connection();

    $isSearching = ($deviceName !== '' || $teacherId !== '' || $classroomId !== '');

    $sql = "SELECT d.id as device_id,
                   d.name as device_name,
                   t.id as transaction_id,
                   t.returned_date,
                   t.teacher_id,
                   t.classroom_id
            FROM devices d
            LEFT JOIN (
                SELECT t1.*
                FROM transactions t1
                JOIN (
                    SELECT device_id, MAX(id) AS max_id
                    FROM transactions
                    GROUP BY device_id
                ) latest ON latest.device_id = t1.device_id AND latest.max_id = t1.id
            ) t ON t.device_id = d.id
            WHERE 1=1";

    $params = [];

    if ($isSearching) {
        $sql .= " AND t.id IS NOT NULL AND (t.returned_date IS NULL OR t.returned_date = '')";
    }

    if ($deviceName !== '') {
        $sql .= " AND d.name LIKE ?";
        $params[] = "%$deviceName%";
    }
    if ($teacherId !== '') {
        $sql .= " AND t.teacher_id = ?";
        $params[] = $teacherId;
    }
    if ($classroomId !== '') {
        $sql .= " AND t.classroom_id = ?";
        $params[] = $classroomId;
    }

    $sql .= " ORDER BY d.id DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $data = [];
    foreach ($rows as $row) {
        $hasTransaction = !empty($row['transaction_id']);
        $isBorrowed = $hasTransaction && ($row['returned_date'] === null || $row['returned_date'] === '');
        
        $data[] = [
            'device_id' => $row['device_id'],
            'device_name' => $row['device_name'],
            'status' => $isBorrowed ? 'borrowed' : 'available',
            'status_label' => $isBorrowed ? 'Đang mượn' : 'Đang rảnh',
            'can_return' => $isBorrowed, 
        ];
    }

    jsonResponse(['status' => 'success', 'data' => $data]);
} catch (Throwable $e) {
    jsonResponse(['status' => 'error', 'error' => $e->getMessage()], 500);
}