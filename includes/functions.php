<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

function e($value) {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function sanxss($value) {
    return strip_tags($value);
}

function redirect($path) {
    header('Location: ' . $path);
    exit;
}

function getBaseUrl() {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host;
}

function getSecurityBadge($level) {
    $map = [
        'low'    => ['Low', 'danger'],
        'medium' => ['Medium', 'warning'],
        'high'   => ['High', 'success']
    ];
    $lvl = $map[$level] ?? $map['medium'];
    return '<span class="badge badge-' . $lvl[1] . '"><span class="badge-dot"></span>' . $lvl[0] . '</span>';
}

function getModeBadge($isSecure) {
    return $isSecure
        ? '<span class="badge badge-success">Patched</span>'
        : '<span class="badge badge-danger">Vulnerable</span>';
}

function isSecureMode($namespace) {
    if (!isset($_SESSION['security_mode'])) {
        $_SESSION['security_mode'] = [];
    }
    return !empty($_SESSION['security_mode'][$namespace]);
}

function toggleSetting($namespace) {
    if (!isset($_SESSION['security_mode'])) {
        $_SESSION['security_mode'] = [];
    }
    $_SESSION['security_mode'][$namespace] = !empty($_SESSION['security_mode'][$namespace]) ? false : true;
    redirect($_SERVER['HTTP_REFERER'] ?? '/index.php');
}

function getSessionStats() {
    $count = 0;
    foreach (session_get_cookie_params() as $p) { $count++; }
    return [
        'session_id'  => session_id(),
        'session_name'=> session_name(),
        'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
        'ip'          => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        'count'       => count($_SESSION)
    ];
}

function renderWarningBanner() {
    return '
    <div class="warning-banner">
        <span class="warning-icon">&#9888;</span>
        <div>
            <strong>LABORATORIUM PEMBELAJARAN - HANYA UNTUK LOCALHOST</strong><br>
            Jangan gunakan di lingkungan produksi atau untuk menyerang sistem lain
        </div>
    </div>';
}

function codePreview($title, $code, $lang = 'php') {
    $colors = ['php' => 'var(--warning)', 'html' => 'var(--primary)', 'js' => 'var(--success)'];
    $color = $colors[$lang] ?? 'var(--primary)';
    return '<div class="terminal" style="color:' . $color . ';">'
        . '<div class="terminal-header"><span class="terminal-dot" style="background:#ff4757;"></span>'
        . '<span class="terminal-dot" style="background:#ffa502;"></span>'
        . '<span class="terminal-dot" style="background:#00ff88;"></span></div>'
        . '<strong>' . e($title) . '</strong>' . "\n" . '<code>' . e($code) . '</code></div>';
}

function renderHeader($headers) {
    $out = '<pre class="terminal">';
    foreach ($headers as $key => $value) {
        $out .= e("$key: $value") . "\n";
    }
    $out .= '</pre>';
    return $out;
}

function rawRequestDump() {
    $method = $_SERVER['REQUEST_METHOD'];
    $uri = $_SERVER['REQUEST_URI'];
    $proto = $_SERVER['SERVER_PROTOCOL'];
    $out = "$method $uri $proto\n";
    $out .= "Host: " . ($_SERVER['HTTP_HOST'] ?? '') . "\n";
    foreach ($_SERVER as $key => $value) {
        if (strpos($key, 'HTTP_') === 0 && in_array($key, ['HTTP_ACCEPT','HTTP_USER_AGENT','HTTP_REFERER','HTTP_COOKIE','HTTP_ACCEPT_LANGUAGE','HTTP_ACCEPT_ENCODING','HTTP_CONNECTION','HTTP_CACHE_CONTROL','HTTP_ORIGIN','HTTP_SEC_FETCH_MODE','HTTP_SEC_FETCH_SITE','HTTP_SEC_FETCH_DEST','HTTP_SEC_CH_UA','HTTP_SEC_CH_UA_MOBILE','HTTP_SEC_CH_UA_PLATFORM'])) {
            $name = str_replace('_', '-', substr($key, 5));
            $name = ucwords(strtolower($name), '-');
            $out .= "$name: $value\n";
        }
    }
    return $out;
}
