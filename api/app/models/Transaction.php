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
}
