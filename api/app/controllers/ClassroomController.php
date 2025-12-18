<?php
require_once __DIR__ . '/../models/Classroom.php';

class ClassroomController
{
    public function index()
    {
        $items = Classroom::all();
        jsonResponse($items);
    }
}
