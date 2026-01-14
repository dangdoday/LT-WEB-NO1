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
        $stmt = $pdo->prepare('SELECT * FROM classrooms WHERE id = :id LIMIT 1');
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

    public static function update($id, $data)
    {
        $pdo = get_db_connection();
        $fields = [];
        $params = ['id' => $id];

        if (isset($data['name'])) {
            $fields[] = 'name = :name';
            $params['name'] = $data['name'];
        }
        if (isset($data['avatar'])) {
            $fields[] = 'avatar = :avatar';
            $params['avatar'] = $data['avatar'];
        }
        if (isset($data['description'])) {
            $fields[] = 'description = :description';
            $params['description'] = $data['description'];
        }
        if (isset($data['building'])) {
            $fields[] = 'building = :building';
            $params['building'] = $data['building'];
        }

        $fields[] = 'updated = CURRENT_TIMESTAMP';

        if (empty($fields)) {
            return false;
        }

        $sql = 'UPDATE classrooms SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
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
        $pdo->beginTransaction();
        try {
            // Set classroom_id to NULL for related transactions
            $stmt = $pdo->prepare("UPDATE transactions SET classroom_id = NULL WHERE classroom_id = :id");
            $stmt->execute(['id' => $id]);

            $stmt = $pdo->prepare("DELETE FROM classrooms WHERE id = :id");
            $result = $stmt->execute(['id' => $id]);
            
            $pdo->commit();
            return $result;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}