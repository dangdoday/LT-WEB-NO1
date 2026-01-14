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
        $id = isset($_GET['id']) ? $_GET['id'] : null;

        if ($id) {
            $item = Classroom::findById($id);
            if ($item) {
                jsonResponse($item);
            } else {
                jsonResponse(['error' => 'Not found'], 404);
            }
            return;
        }

        $building = isset($_GET['building']) ? $_GET['building'] : '';
        $keyword  = isset($_GET['keyword']) ? $_GET['keyword'] : '';

        $items = Classroom::search($building, $keyword);
        
        jsonResponse($items);
    }

    public function delete() 
    {
        // Lấy ID từ URL
        $id = $_GET['id'] ?? null;

        if (!$id) {
            jsonResponse(['error' => 'Chưa cung cấp ID phòng học'], 400);
            return;
        }

        $result = Classroom::delete($id);

        if ($result) {
            jsonResponse(['success' => true, 'message' => 'Đã xóa thành công']);
        } else {
            jsonResponse(['error' => 'Xóa thất bại'], 500);
        }
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

    public function update()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            jsonResponse(['error' => 'Missing ID'], 400);
            return;
        }

        // validate input
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $building = trim($_POST['building'] ?? '');

        // Basic validation similar to create
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

        if (!empty($errors)) {
            http_response_code(422);
            jsonResponse(['error' => 'Validation failed', 'fields' => $errors]);
            return;
        }

        try {
            $file = (!empty($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) ? $_FILES['avatar'] : null;
            
            $this->service->update($id, [
                'name' => $name,
                'description' => $description,
                'building' => $building,
            ], $file);

            jsonResponse(['success' => true, 'message' => 'Update successful']);
        } catch (Throwable $e) {
            http_response_code(500);
            jsonResponse(['error' => 'Internal server error', 'message' => $e->getMessage()]);
        }
    }
}