<?php
class Teacher
{
    public static function all()
    {
        $pdo = get_db_connection();
        $stmt = $pdo->query('SELECT id, name FROM teachers LIMIT 100');
        return $stmt->fetchAll();
    }
}
