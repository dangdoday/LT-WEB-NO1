<?php
require_once __DIR__ . '/../models/User.php';

class UserController
{
    public function index()
    {
        $users = User::all();
        jsonResponse($users);
    }

    public function create()
    {
        $data = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $user = User::create($data);
        jsonResponse($user, 201);
    }
}
