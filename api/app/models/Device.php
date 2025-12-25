<?php
class Device
{
    public static function all()
    {
        $pdo = get_db_connection();
        $stmt = $pdo->query('SELECT id, name, serial FROM devices LIMIT 100');
        return $stmt->fetchAll();
    }

    public static function findById($id)
    {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare('SELECT id, name, serial FROM devices WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public static function search($keyword = '', $status = '')
    {
        $pdo = get_db_connection();
        
        $sql = "SELECT d.*, 
                CASE 
                    WHEN EXISTS (SELECT 1 FROM transactions t WHERE t.device_id = d.id AND (t.returned_date IS NULL OR t.returned_date = '')) THEN 'Đang mượn'
                    ELSE 'Đang rảnh'
                END as status
                FROM devices d
                WHERE 1=1";
        
        $params = [];
        if (!empty($keyword)) {
            $sql .= " AND (d.name LIKE ? OR d.description LIKE ?)";
            $params[] = "%$keyword%";
            $params[] = "%$keyword%";
        }
        
        if ($status === 'Đang mượn') {
            $sql .= " AND EXISTS (SELECT 1 FROM transactions t WHERE t.device_id = d.id AND (t.returned_date IS NULL OR t.returned_date = ''))";
        } elseif ($status === 'Đang rảnh') {
            $sql .= " AND NOT EXISTS (SELECT 1 FROM transactions t WHERE t.device_id = d.id AND (t.returned_date IS NULL OR t.returned_date = ''))";
        }
        
        $sql .= " ORDER BY d.id DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
