<?php
require_once __DIR__ . '/../common/db.php';

function checkLogin($login_id, $password) {
    global $conn;

    $sql = "SELECT * FROM admins WHERE login_id = :login_id";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['login_id' => $login_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$admin) return false;

    if (!password_verify($password, $admin['password'])) {
        return false;
    }

    return $admin;
}
