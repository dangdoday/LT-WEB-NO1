<?php
require_once __DIR__ . '/app/common/define.php';
require_once __DIR__ . '/app/common/db.php';
require_once __DIR__ . '/app/helpers/response.php';

header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    jsonResponse(['error' => 'Missing id'], 400);
    exit;
}

try {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare('SELECT id, name, description, avatar FROM devices WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $device = $stmt->fetch();

    if (!$device) {
        jsonResponse(['error' => 'Not found'], 404);
        exit;
    }

    $device['avatar_url'] = $device['avatar'] ? '/api/web/avatar/' . $device['avatar'] : null;
    jsonResponse(['status' => 'success', 'data' => $device]);
} catch (Throwable $e) {
    jsonResponse(['error' => 'Server error'], 500);
}
