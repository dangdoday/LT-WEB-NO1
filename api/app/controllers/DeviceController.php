<?php
require_once __DIR__ . '/../models/Device.php';

class DeviceController
{
    public function index()
    {
        $devices = Device::all();
        jsonResponse($devices);
    }
}
