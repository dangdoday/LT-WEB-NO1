<?php
// Return an array of routes: $routes[METHOD][PATH] = [controllerFile, class, action]
return [
    'GET' => [
        '/' => ['/app/controllers/AuthController.php', 'AuthController', 'index'],
        '/users' => ['/app/controllers/UserController.php', 'UserController', 'index'],
        '/classrooms' => ['/app/controllers/ClassroomController.php', 'ClassroomController', 'index'],
        '/devices' => ['/app/controllers/DeviceController.php', 'DeviceController', 'index'],
    ],
    'POST' => [
        '/auth/login' => ['/app/controllers/AuthController.php', 'AuthController', 'login'],
        '/users' => ['/app/controllers/UserController.php', 'UserController', 'create'],
    ],
    'PUT' => [
    ],
    'DELETE' => [
    ],
];
