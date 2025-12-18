<?php
require_once __DIR__ . '/../models/User.php';

class AuthController
{
    public function index()
    {
        jsonResponse(['message' => 'Auth index']);
    }

    public function login()
    {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        if (empty($input['email']) || empty($input['password'])) {
            jsonResponse(['error' => 'Email and password required'], 400);
            return;
        }

        $user = User::findByEmail($input['email']);
        if (!$user || $user['password'] !== $input['password']) {
            jsonResponse(['error' => 'Invalid credentials'], 401);
            return;
        }

        // Not a real JWT - placeholder token
        $token = base64_encode($user['email'] . '|' . time());
        jsonResponse(['token' => $token, 'user' => $user]);
    }
}
