<?php
require_once __DIR__ . '/../models/Classroom.php';
require_once __DIR__ . '/../services/ClassroomService.php';

class ClassroomController
{
    private ClassroomService $service;

    public function __construct()
    {
        $this->service = new ClassroomService();
    }
    public function index()
    {
        $items = Classroom::all();
        jsonResponse($items);
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            jsonResponse(['error' => 'Method not allowed'], 405);
            return;
        }

        // validate input
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $building = trim($_POST['building'] ?? '');

        $errors = [];
        if ($name === '') {
            $errors['name'] = 'Please enter the classroom name.';
        } elseif (strlen($name) > 100) {
            $errors['name'] = 'Classroom name is too long.';
        }

        if ($description === '') {
            $errors['description'] = 'Please enter a description.';
        } elseif (strlen($description) > 1000) {
            $errors['description'] = 'Description is too long.';
        }

        if ($building === '') {
            $errors['building'] = 'Please select a building.';
        }

        if (empty($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            $errors['avatar'] = 'Please select an avatar image.';
        }

        if (!empty($errors)) {
            http_response_code(422);
            jsonResponse(['error' => 'Validation failed', 'fields' => $errors]);
            return;
        }

        try {
            $result = $this->service->create([
                'name' => $name,
                'description' => $description,
                'building' => $building,
            ], $_FILES['avatar']);

            jsonResponse(['success' => true, 'id' => $result['id'], 'avatar' => $result['avatar'], 'description' => $result['description'], 'building' => $result['building']]);
        } catch (Throwable $e) {
            http_response_code(500);
            jsonResponse(['error' => 'Internal server error', 'message' => $e->getMessage()]);
        }
    }
}
