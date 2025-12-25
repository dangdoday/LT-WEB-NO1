<?php

$apiRootDir = dirname(__DIR__, 2);

define('DB_PATH', $apiRootDir . '/storage/ltweb.sqlite');

define('BASE_PATH', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));

$configEnvPath = $apiRootDir . '/config_env.php';

if (file_exists($configEnvPath)) {
    $CONFIG = require $configEnvPath;
} else {
    $CONFIG = [];
}