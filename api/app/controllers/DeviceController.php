<?php
require_once __DIR__ . '/../models/Device.php';

class DeviceController
{
    public function index()
    {
        $devices = Device::all();
        jsonResponse($devices);
    }

    public function advancedSearch()
    {
        $keyword = $_GET['keyword'] ?? '';
        $status = $_GET['status'] ?? '';

        $devices = Device::search($keyword, $status);
        jsonResponse($devices);
    }

    public function show()
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $name = trim($_GET['name'] ?? '');

        if ($id <= 0 && $name === '') {
            jsonResponse(['error' => 'Invalid device id or name'], 400);
            return;
        }

        if ($id > 0) {
            $device = Device::findById($id);
        } else {
            $device = Device::findByName($name);
        }

        if (!$device) {
            jsonResponse(['error' => 'Device not found'], 404);
            return;
        }
        jsonResponse(['device' => $device]);
    }

    public function update()
    {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        $errors = [];
        if ($id <= 0) {
            $errors['id'] = 'Invalid device id';
        }
        if ($name === '') {
            $errors['name'] = 'Please enter device name';
        }
        if ($description === '') {
            $errors['description'] = 'Please enter description';
        } elseif (strlen($description) > 1000) {
            $errors['description'] = 'Description must be <= 1000 characters';
        }

        $existing = $id > 0 ? Device::findById($id) : null;
        if ($id > 0 && !$existing) {
            $errors['id'] = 'Device not found';
        }

        $avatarFileName = null;
        $avatarProvided = isset($_FILES['avatar']) && is_array($_FILES['avatar']) && ($_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE);

        if ($avatarProvided) {
            $file = $_FILES['avatar'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $errors['avatar'] = 'Upload failed';
            } else {
                $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                if (!in_array($mime, $allowed, true)) {
                    $errors['avatar'] = 'Invalid image type';
                }
            }
        }

        if (!empty($errors)) {
            jsonResponse(['error' => 'Validation failed', 'fields' => $errors], 422);
            return;
        }

        if ($avatarProvided) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $avatarFileName = 'device_' . $id . '_' . time() . '.' . $ext;
            $destDir = __DIR__ . '/../../web/avatar';
            if (!is_dir($destDir)) {
                @mkdir($destDir, 0775, true);
            }
            $destPath = $destDir . '/' . $avatarFileName;
            if (!move_uploaded_file($file['tmp_name'], $destPath)) {
                jsonResponse(['error' => 'Cannot save avatar'], 500);
                return;
            }
        }

        $avatarToSave = $avatarProvided ? $avatarFileName : null;
        Device::updateItem($id, $name, $description, $avatarToSave);

        jsonResponse([
            'message' => 'Updated',
            'avatar' => $avatarToSave ?? ($existing['avatar'] ?? null),
        ]);
    }
}
