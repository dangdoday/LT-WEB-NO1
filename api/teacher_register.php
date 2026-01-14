<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}
require_once 'app/common/db.php';
require_once 'app/helpers/response.php';
require_once 'app/controllers/TeacherController.php';

$controller = new TeacherController();
$id = $_POST['id'] ?? $_GET['id'] ?? null;

if ($id) {
    // UPDATE
    $controller->update();
} else {
    // CREATE
    $controller->create();
}
?>
