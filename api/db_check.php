<?php
require_once __DIR__ . '/app/common/define.php';

header('Content-Type: text/plain; charset=utf-8');

if (!extension_loaded('pdo_sqlite')) {
    http_response_code(500);
    echo 'Ket noi that bai (missing pdo_sqlite)';
    exit;
}

try {
    $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');
    echo 'Ket noi thanh cong';
} catch (PDOException $e) {
    http_response_code(500);
    echo 'Ket noi that bai';
}
