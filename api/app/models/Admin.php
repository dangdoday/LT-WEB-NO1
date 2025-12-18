<?php
class Admin
{
    public static function all()
    {
        $pdo = get_db_connection();
        $stmt = $pdo->query('SELECT id, name FROM admins LIMIT 100');
        return $stmt->fetchAll();
    }
}
