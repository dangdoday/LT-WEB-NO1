<?php
// Basic defines for the API (PostgreSQL local defaults)
define('DB_DRIVER', 'pgsql');
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '5432');
define('DB_NAME', 'ltweb');
define('DB_USER', 'postgres');
define('DB_PASS', '');

// You can set BASE_PATH or BASE_URL if needed
define('BASE_PATH', rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'));
