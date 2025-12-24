<?php
// Simple front controller / router
require_once __DIR__ . '/app/config/define.php';
require_once __DIR__ . '/app/config/db.php';

require_once __DIR__ . '/app/helpers/response.php';
require_once __DIR__ . '/app/helpers/auth.php';

// cors
header('Access-Control-Allow-Origin: http://localhost:5173');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$routes = require __DIR__ . '/routes/api.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Normalize base path if script is not in root
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
if ($base !== '') {
    $uri = preg_replace('#^' . preg_quote($base) . '#', '', $uri);
}

// Simple dispatch: look for exact match, then method
if (isset($routes[$method][$uri])) {
    $handler = $routes[$method][$uri];
    list($controllerFile, $class, $action) = $handler;
    require_once __DIR__ . $controllerFile;
    $ctrl = new $class();
    $ctrl->$action();
    exit;
}

// Not found
http_response_code(404);
jsonResponse(['error' => 'Not Found'], 404);
