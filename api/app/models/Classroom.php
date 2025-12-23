<?php
class Classroom
{
    public static function all()
    {
        $pdo = get_db_connection();
        $stmt = $pdo->query('SELECT id, name FROM classrooms LIMIT 100');
        return $stmt->fetchAll();
    }

    public static function findById($id)
    {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare('SELECT id, name FROM classrooms WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
}
