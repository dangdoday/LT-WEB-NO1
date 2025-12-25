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
}
