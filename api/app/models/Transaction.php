<?php
class Transaction
{
    public static function all()
    {
        $pdo = get_db_connection();
        $stmt = $pdo->query('SELECT id, user_id, device_id, type, created_at FROM transactions ORDER BY created_at DESC LIMIT 100');
        return $stmt->fetchAll();
    }
}
