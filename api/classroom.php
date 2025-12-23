<?php
require_once __DIR__ . '/app/config/define.php';
require_once __DIR__ . '/app/config/db.php';
require_once __DIR__ . '/app/helpers/response.php';
require_once __DIR__ . '/app/controllers/ClassroomController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
    exit;
}

$controller = new ClassroomController();
$controller->index();
