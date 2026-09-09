<?php
require_once __DIR__ . '/config.php';

try {
    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec('PRAGMA journal_mode=WAL');
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

function initDatabase() {
    global $db;

    $db->exec("CREATE TABLE IF NOT EXISTS comments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL,
        message TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS activity_log (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        event_type TEXT NOT NULL,
        details TEXT,
        ip_address TEXT,
        user_agent TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $db->exec("CREATE TABLE IF NOT EXISTS payload_log (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        payload TEXT,
        source TEXT,
        executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
}

function logActivity($type, $details = '', $ip = '', $ua = '') {
    global $db;
    $ip = $ip ?: $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $ua = $ua ?: $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

    $stmt = $db->prepare("INSERT INTO activity_log (event_type, details, ip_address, user_agent) VALUES (?, ?, ?, ?)");
    $stmt->execute([$type, $details, $ip, $ua]);

    if (file_exists(ACTIVITY_LOG)) {
        $line = date('Y-m-d H:i:s') . " | $type | $details | $ip\n";
        file_put_contents(ACTIVITY_LOG, $line, FILE_APPEND);
    }
}

function logPayload($payload, $source = 'unknown') {
    global $db;
    $stmt = $db->prepare("INSERT INTO payload_log (payload, source) VALUES (?, ?)");
    $stmt->execute([$payload, $source]);
    logActivity('payload_executed', "Source: $source | Payload: " . substr($payload, 0, 200));
}

function getStats() {
    global $db;
    $payloads = $db->query("SELECT COUNT(*) FROM payload_log")->fetchColumn();
    $attacks = $db->query("SELECT COUNT(*) FROM activity_log WHERE event_type = 'payload_executed'")->fetchColumn();
    $comments = $db->query("SELECT COUNT(*) FROM comments")->fetchColumn();
    $logs = $db->query("SELECT COUNT(*) FROM activity_log")->fetchColumn();

    return [
        'total_payloads' => $payloads,
        'total_attacks' => $attacks,
        'total_comments' => $comments,
        'total_logs' => $logs
    ];
}

function resetLab() {
    global $db;
    $db->exec("DELETE FROM comments");
    $db->exec("DELETE FROM activity_log");
    $db->exec("DELETE FROM payload_log");

    $uploadDir = UPLOAD_DIR;
    if (is_dir($uploadDir)) {
        $files = glob($uploadDir . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    if (file_exists(COOKIE_LOG)) {
        file_put_contents(COOKIE_LOG, '');
    }
    if (file_exists(ACTIVITY_LOG)) {
        file_put_contents(ACTIVITY_LOG, '');
    }

    session_destroy();
    header('Location: /index.php?reset=1');
    exit;
}

function getRecentLogs($limit = 20) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM activity_log ORDER BY created_at DESC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCookieLogs($limit = 20) {
    if (!file_exists(COOKIE_LOG)) return [];
    $lines = file(COOKIE_LOG, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$lines) return [];
    return array_slice(array_reverse($lines), 0, $limit);
}

initDatabase();
