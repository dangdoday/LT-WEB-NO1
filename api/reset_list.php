<?php
require_once __DIR__ . '/app/common/define.php';
require_once __DIR__ . '/app/common/db.php';
require_once __DIR__ . '/app/helpers/response.php';
require_once __DIR__ . '/app/models/Admin.php';

header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
    exit;
}

$items = Admin::listPendingReset();
jsonResponse(['items' => $items]);