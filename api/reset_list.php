<?php
require_once __DIR__ . '/app/common/define.php';
require_once __DIR__ . '/app/common/db.php';
require_once __DIR__ . '/app/helpers/response.php';
require_once __DIR__ . '/app/models/Admin.php';

header('Access-Control-Allow-Origin: *');

session_start();
if (empty($_SESSION['login_id'])) {
    jsonResponse(['error' => 'Unauthorized'], 401);
    exit;
}
if (strtolower($_SESSION['login_id']) !== 'admin') {
    jsonResponse(['error' => 'Forbidden'], 403);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
    exit;
}

$items = Admin::listPendingReset();
jsonResponse(['items' => $items]);
