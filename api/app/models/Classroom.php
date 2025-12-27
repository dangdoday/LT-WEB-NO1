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

    public static function create($data)
    {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare('INSERT INTO classrooms (name, avatar, description, building, updated, created) VALUES (:name, :avatar, :description, :building, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)');
        $stmt->execute([
            'name' => $data['name'] ?? null,
            'avatar' => $data['avatar'] ?? null,
            'description' => $data['description'] ?? null,
            'building' => $data['building'] ?? null,
        ]);
        return $pdo->lastInsertId();
    }

    public static function search($building = '', $keyword = '')
    {
        $pdo = get_db_connection();
        
        $sql = "SELECT * FROM classrooms WHERE 1=1";
        $params = [];

        if (!empty($building)) {
            $sql .= " AND building = :building";
            $params['building'] = $building;
        }

        if (!empty($keyword)) {
            $sql .= " AND (name LIKE :keyword OR description LIKE :keyword)";
            $params['keyword'] = "%$keyword%";
        }

        $sql .= " ORDER BY id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function delete($id)
    {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("DELETE FROM classrooms WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}