<?php
require_once __DIR__ . '/../../includes/functions.php';

$currentPage = 'cmd.php';
$pageTitle = 'Command Injection';
$pageSubtitle = 'Remote Code Execution via Command Injection';

$secure = isSecureMode('rce_cmd');
$isWin = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
$pingFlag = $isWin ? '-n' : '-c';
$whoamiCmd = $isWin ? 'whoami' : 'id';

if (isset($_POST['toggle'])) {
    toggleSetting('rce_cmd');
}

$output = null;
$cmd = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ip'])) {
    $ip = trim($_POST['ip']);

    if ($secure) {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            $error = 'Alamat IP tidak valid. Hanya IPv4/IPv6 murni yang diizinkan.';
        } else {
            $cmd = 'ping ' . $pingFlag . ' 1 ' . escapeshellarg($ip);
            exec($cmd, $outputLines, $code);
            $output = implode("\n", $outputLines);
        }
    } else {
        if (preg_match('/[;&|`$\n\r]/', $ip)) {
            logPayload($ip, 'rce_cmd');
        }
        $cmd = 'ping ' . $pingFlag . ' 1 ' . $ip;
        exec($cmd, $outputLines, $code);
        $output = implode("\n", $outputLines);
    }
}

$vulnCode = <<<'PHP'
// VULNERABLE - input digabung langsung
$cmd = "ping -n 1 " . $_POST['ip'];
system($cmd);
// Input: 127.0.0.1; whoami  => dua perintah
PHP;

$secureCode = <<<'PHP'
// SECURE - validasi + escape
if (!filter_var($ip, FILTER_VALIDATE_IP)) die('invalid');
$cmd = "ping -n 1 " . escapeshellarg($ip);
PHP;

ob_start();
?>
<div class="card">
    <div class="card-header">
        <h3><span class="card-icon">&#128225;</span> Ping Tool</h3>
        <div style="display:flex;gap:10px;align-items:center;">
            <?php echo getSecurityBadge('high'); ?>
            <?php echo getModeBadge($secure); ?>
        </div>
    </div>
    <div class="card-body">
        <?php if ($secure): ?>
            <div class="alert alert-success"><span class="alert-icon">&#10004;</span>
                <div><strong>Mode SECURE aktif.</strong> IP divalidasi (IPv4/IPv6 murni) lalu di-escape. Payload seperti <code><?php echo e('127.0.0.1; ' . $whoamiCmd); ?></code> tidak akan mengeksekusi perintah ekstra.</div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger"><span class="alert-icon">&#9888;</span>
                <div><strong>Mode VULNERABLE aktif.</strong> Input digabung langsung ke command. Coba <code><?php echo e('127.0.0.1; ' . $whoamiCmd); ?></code> atau <code><?php echo e('127.0.0.1 | dir'); ?></code>.</div>
            </div>
        <?php endif; ?>

        <form method="post" action="/rce/cmd.php">
            <div class="floating-label">
                <input type="text" name="ip" id="ip" placeholder="contoh: 127.0.0.1" value="<?php echo isset($ip) ? e($ip) : ''; ?>">
                <label for="ip">IP Address / Host</label>
            </div>
            <button type="submit" class="btn btn-primary">&#128225; Ping</button>
        </form>
        <form method="post" action="/rce/cmd.php">
            <input type="hidden" name="toggle" value="1">
            <button type="submit" class="btn <?php echo $secure ? 'btn-danger' : 'btn-success'; ?>">
                <?php echo $secure ? '&#128683; Beralih ke Vulnerable' : '&#128274; Beralih ke Secure'; ?>
            </button>
        </form>
    </div>
</div>

<?php if (isset($error)): ?>
<div class="alert alert-danger"><span class="alert-icon">&#9888;</span>
    <div><strong>Input ditolak.</strong> <?php echo $error; ?></div>
</div>
<?php endif; ?>

<?php if ($cmd !== '' && (!isset($error))): ?>
<div class="card">
    <div class="card-header">
        <h3><span class="card-icon">&#128187;</span> Output Terminal</h3>
        <span class="badge <?php echo $secure ? 'badge-success' : 'badge-danger'; ?>">
            <?php echo $secure ? 'Sanitized' : 'Raw Execution'; ?>
        </span>
    </div>
    <div class="card-body">
        <div class="terminal">
            <div class="terminal-header"><span class="terminal-dot" style="background:#ff4757;"></span><span class="terminal-dot" style="background:#ffa502;"></span><span class="terminal-dot" style="background:#00ff88;"></span></div>
            <span class="text-muted">$ </span><?php echo e($cmd); ?><br><br>
            <?php echo e($output !== null ? $output : '(tanpa output)'); ?>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3><span class="card-icon">&#128295;</span> Perbandingan Kode</h3>
    </div>
    <div class="card-body flex gap-2">
        <div style="flex:1;"><?php echo codePreview('vulnerable', $vulnCode); ?></div>
        <div style="flex:1;"><?php echo codePreview('patched', $secureCode); ?></div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../includes/layout.php';