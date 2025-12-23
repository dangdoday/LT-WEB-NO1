<?php
require_once __DIR__ . '/app/common/define.php';
require_once __DIR__ . '/app/common/db.php';
require_once __DIR__ . '/app/helpers/response.php';
require_once __DIR__ . '/app/models/Device.php';
require_once __DIR__ . '/app/models/Teacher.php';
require_once __DIR__ . '/app/models/Classroom.php';

header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
    exit;
}

try {
    $devices = Device::all();
    $teachers = Teacher::all();
    $classrooms = Classroom::all();

    jsonResponse([
        'devices' => $devices,
        'teachers' => $teachers,
        'classrooms' => $classrooms,
    ]);
} catch (Throwable $e) {
    jsonResponse(['error' => 'Server error'], 500);
}
