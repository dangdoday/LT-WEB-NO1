<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/app/config/define.php';
require_once __DIR__ . '/app/config/db.php';
require_once __DIR__ . '/app/helpers/response.php';
require_once __DIR__ . '/app/controllers/ClassroomController.php';

$controller = new ClassroomController();
<<<<<<< HEAD
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $controller->index();
=======

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;

if ($method === 'GET') {
    if ($id) {
        $controller->show();
    } else {
        $controller->index();
    }
} elseif ($method === 'POST') {
    if ($id) {
        $controller->update();
    } else {
        $controller->create();
    }
>>>>>>> 12c0183 (Update ClassroomEdit UI styling and translation)
} elseif ($method === 'DELETE') {
    $controller->delete();
} else {
    jsonResponse(['error' => 'Method not allowed'], 405);
}
<<<<<<< HEAD
?>
=======
>>>>>>> 12c0183 (Update ClassroomEdit UI styling and translation)
