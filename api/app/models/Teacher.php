<?php
class Teacher
{
    public static function updateWithAvatar($id, $data)
    {
        $pdo = get_db_connection();
        // Nếu có avatar mới thì update, không thì giữ nguyên
        if (isset($data['avatar']) && $data['avatar']) {
            $stmt = $pdo->prepare(
                'UPDATE teachers
                 SET name = :name,
                     specialized = :specialized,
                     description = :description,
                     degree = :degree,
                     avatar = :avatar
                 WHERE id = :id'
            );
            return $stmt->execute([
                'id' => $id,
                'name' => $data['name'] ?? '',
                'specialized' => $data['specialized'] ?? '',
                'description' => $data['description'] ?? '',
                'degree' => $data['degree'] ?? '',
                'avatar' => $data['avatar'],
            ]);
        } else {
            $stmt = $pdo->prepare(
                'UPDATE teachers
                 SET name = :name,
                     specialized = :specialized,
                     description = :description,
                     degree = :degree
                 WHERE id = :id'
            );
            return $stmt->execute([
                'id' => $id,
                'name' => $data['name'] ?? '',
                'specialized' => $data['specialized'] ?? '',
                'description' => $data['description'] ?? '',
                'degree' => $data['degree'] ?? '',
            ]);
        }
    }
    public static function all()
    {
        $pdo = get_db_connection();
        return $pdo->query('SELECT id, name FROM teachers LIMIT 100')->fetchAll();
    }

    public static function allFull()
    {
        $pdo = get_db_connection();
        return $pdo->query(
            'SELECT id, name, specialized, description, degree, avatar FROM teachers ORDER BY id DESC'
        )->fetchAll();
    }

    public static function findById($id)
    {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare(
            'SELECT id, name, specialized, description, degree, avatar FROM teachers WHERE id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public static function search($specialized = null, $keyword = null)
    {
        $pdo = get_db_connection();
        $sql = "SELECT id, name, specialized, description, degree, avatar FROM teachers WHERE 1=1";
        $params = [];
        if (!empty($specialized)) {
            $sql .= " AND specialized = :specialized";
            $params['specialized'] = $specialized;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }



    public static function deleteById($id)
    {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare('DELETE FROM teachers WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public static function getSpecializedList()
    {
        $pdo = get_db_connection();
        return $pdo->query(
            'SELECT DISTINCT specialized FROM teachers
             WHERE specialized IS NOT NULL AND specialized != ""
             ORDER BY specialized'
        )->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function update($id, $data)
    {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare(
            'UPDATE teachers
             SET name = :name,
                 specialized = :specialized,
                 description = :description,
                 degree = :degree
             WHERE id = :id'
        );
        return $stmt->execute([
            'id' => $id,
            'name' => $data['name'] ?? '',
            'specialized' => $data['specialized'] ?? '',
            'description' => $data['description'] ?? '',
            'degree' => $data['degree'] ?? '',
        ]);
    }

    public static function create(array $data)
    {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare(
            'INSERT INTO teachers (name, specialized, degree, description, avatar)
             VALUES (:name, :specialized, :degree, :description, :avatar)'
        );
        $stmt->execute([
            'name' => $data['name'] ?? '',
            'specialized' => $data['specialized'] ?? '',
            'degree' => $data['degree'] ?? '',
            'description' => $data['description'] ?? '',
            'avatar' => $data['avatar'] ?? '',
        ]);
        return $pdo->lastInsertId();
    }
}