<?php
class User
{
    public static function all()
    {
        $pdo = get_db_connection();
        $stmt = $pdo->query('SELECT id, name, email FROM users LIMIT 100');
        return $stmt->fetchAll();
    }

    public static function findByEmail($email)
    {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }

    public static function create($data)
    {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (:name, :email, :password)');
        $stmt->execute([
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'password' => $data['password'] ?? null,
        ]);
        $id = $pdo->lastInsertId();

        return ['id' => $id, 'name' => $data['name'] ?? null, 'email' => $data['email'] ?? null];
    }
}
