<?php
define('APP_NAME', 'Security Lab - XSS to RCE');
define('APP_VERSION', '1.0.0');
define('DB_PATH', __DIR__ . '/../database/lab.db');
define('UPLOAD_DIR', __DIR__ . '/../public/uploads/');
define('LOG_DIR', __DIR__ . '/../logs/');
define('COOKIE_LOG', LOG_DIR . 'cookies.log');
define('ACTIVITY_LOG', LOG_DIR . 'activity.log');

if (!file_exists(LOG_DIR)) {
    mkdir(LOG_DIR, 0755, true);
}

session_start();

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
