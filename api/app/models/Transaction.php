<?php
class Transaction
{
    public static function all()
    {
        $pdo = get_db_connection();
        $stmt = $pdo->query('SELECT id, user_id, device_id, type, created_at FROM transactions ORDER BY created_at DESC LIMIT 100');
        return $stmt->fetchAll();
    }

    public static function create($data)
    {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare(
            'INSERT INTO transactions (device_id, teacher_id, classroom_id, comment, start_transaction_plan, end_transaction_plan, type, created_at)
             VALUES (:device_id, :teacher_id, :classroom_id, :comment, :start_transaction_plan, :end_transaction_plan, :type, CURRENT_TIMESTAMP)'
        );
        $stmt->execute([
            'device_id' => $data['device_id'],
            'teacher_id' => $data['teacher_id'],
            'classroom_id' => $data['classroom_id'],
            'comment' => $data['comment'] ?? null,
            'start_transaction_plan' => $data['start_transaction_plan'],
            'end_transaction_plan' => $data['end_transaction_plan'],
            'type' => $data['type'] ?? 'borrow',
        ]);

        return $pdo->lastInsertId();
    }

    public static function search($deviceName = '', $teacherId = '')
    {
        $pdo = get_db_connection();

        $sql = "SELECT t.id, 
                   d.name as device_name, 
                   t.start_transaction_plan, 
                   t.end_transaction_plan, 
                   t.created_at as actual_return_time, 
                   te.name as teacher_name 
            FROM transactions t
            JOIN devices d ON t.device_id = d.id
            JOIN teachers te ON t.teacher_id = te.id
            WHERE 1=1";

        $params = [];
        if (!empty($deviceName)) {
            $sql .= " AND d.name LIKE ?";
            $params[] = "%$deviceName%";
        }
        if (!empty($teacherId)) {
            $sql .= " AND t.teacher_id = ?";
            $params[] = $teacherId;
        }

        $sql .= " ORDER BY t.start_transaction_plan DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
