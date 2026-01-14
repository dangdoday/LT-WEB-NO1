<?php
// Đảm bảo luôn require define.php để có DB_PATH
require_once __DIR__ . '/define.php';

function get_db_connection()
{
    try {
        if (!extension_loaded('pdo_sqlite')) {
            throw new RuntimeException('missing pdo_sqlite');
        }
        // Sử dụng đường dẫn tuyệt đối cho DB_PATH
        $dsn = 'sqlite:' . DB_PATH;
        $pdo = new PDO($dsn, '', '', [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON');
        return $pdo;
    } catch (Throwable $e) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(
            ['error' => 'Database connection failed', 'message' => $e->getMessage()],
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
        );
        exit;
    }
}
