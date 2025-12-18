<?php
require_once __DIR__ . '/../models/Transaction.php';

class BorrowService
{
    public function borrow($userId, $deviceId)
    {
        $pdo = get_db_connection();
        if (defined('DB_DRIVER') && DB_DRIVER === 'pgsql') {
            $stmt = $pdo->prepare('INSERT INTO transactions (user_id, device_id, type, created_at) VALUES (:user_id, :device_id, :type, NOW()) RETURNING id');
            $stmt->execute(['user_id' => $userId, 'device_id' => $deviceId, 'type' => 'borrow']);
            $res = $stmt->fetch();
            $id = $res ? $res['id'] : null;
        } else {
            $stmt = $pdo->prepare('INSERT INTO transactions (user_id, device_id, type, created_at) VALUES (:user_id, :device_id, :type, NOW())');
            $stmt->execute(['user_id' => $userId, 'device_id' => $deviceId, 'type' => 'borrow']);
            $id = $pdo->lastInsertId();
        }

        return ['id' => $id, 'user_id' => $userId, 'device_id' => $deviceId, 'type' => 'borrow'];
    }
}
