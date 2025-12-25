<?php
// Return an array of routes: $routes[METHOD][PATH] = [controllerFile, class, action]
return [
    'GET' => [
        '/' => ['/app/controllers/AuthController.php', 'AuthController', 'index'],
        '/users' => ['/app/controllers/UserController.php', 'UserController', 'index'],
        '/classrooms' => ['/app/controllers/ClassroomController.php', 'ClassroomController', 'index'],
        '/devices' => ['/app/controllers/DeviceController.php', 'DeviceController', 'index'],
        '/devices/advanced-search' => ['/app/controllers/DeviceController.php', 'DeviceController', 'advancedSearch'],
        '/home' => ['/home.php', null, null],
        '/get_config' => ['/get_config.php', null, null],
    ],
    'POST' => [
        '/auth/login' => ['/app/controllers/AuthController.php', 'AuthController', 'login'],
        '/users' => ['/app/controllers/UserController.php', 'UserController', 'create'],
        '/classrooms' => ['/app/controllers/ClassroomController.php', 'ClassroomController', 'create'],
        '/login' => ['/login.php', null, null],
        '/register' => ['/register.php', null, null],
    ],
    'PUT' => [
    ],
    'DELETE' => [
    ],
];
