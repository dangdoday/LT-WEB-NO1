<?php
class Admin
{
    public static function all()
    {
        $pdo = get_db_connection();
        $stmt = $pdo->query('SELECT id, name FROM admins LIMIT 100');
        return $stmt->fetchAll();
    }
    
    public static function findByLoginId($loginId)
    {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare('SELECT * FROM admins WHERE login_id = :login_id LIMIT 1');
        $stmt->execute(['login_id' => $loginId]);
        return $stmt->fetch();
    }

    public static function findById($id)
    {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare('SELECT * FROM admins WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public static function updateResetToken($id, $token)
    {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare('UPDATE admins SET reset_password_token = :token, updated = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute(['token' => $token, 'id' => $id]);
    }

    public static function listPendingReset()
    {
        $pdo = get_db_connection();
        $stmt = $pdo->query("SELECT id, name, login_id FROM admins WHERE reset_password_token IS NOT NULL AND reset_password_token != '' ORDER BY created DESC LIMIT 200");
        return $stmt->fetchAll();
    }

    public static function resetPassword($id, $newPassword)
    {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare('UPDATE admins SET password = :password, reset_password_token = "", updated = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute([
            'password' => md5($newPassword),
            'id' => $id,
        ]);
    }
}
