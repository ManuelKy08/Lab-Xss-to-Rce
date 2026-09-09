<?php
require_once __DIR__ . '/../../includes/functions.php';

$currentPage = 'eval.php';
$currentEval = true;
$pageTitle = 'Eval Injection';
$pageSubtitle = 'Remote Code Execution via eval()';

$secure = isSecureMode('rce_eval');
$isWin = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
$whoamiCmd = $isWin ? 'whoami' : 'id';

if (isset($_POST['toggle'])) {
    toggleSetting('rce_eval');
}

$expr = '';
$result = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['expr'])) {
    $expr = trim($_POST['expr']);

    if ($secure) {
        if (!preg_match('/^[0-9+\-*\/%().\s]+$/', $expr)) {
            $error = 'Ekspresi hanya boleh berisi angka dan operator matematika.';
        } else {
            eval('$result = (' . $expr . ');');
            logActivity('eval_secure', "Kalkulasi aman: $expr = " . var_export($result, true));
        }
    } else {
        if (preg_match('/[;`$]/', $expr)) {
            logPayload($expr, 'rce_eval');
        }
        eval('$result = ' . $expr . ';');
        logActivity('eval_vulnerable', "Ekspresi: $expr");
    }
}

$vulnCode = <<<'PHP'
// VULNERABLE - eval input mentah
$expr = $_POST['expr'];
eval('$result = ' . $expr . ';');
// 2+2; system('whoami') -> RCE
PHP;

$secureCode = <<<'PHP'
// SECURE - whitelist karakter
if (!preg_match('/^[0-9+\-*\/%().\s]+$/', $expr)) {
    die('ekspresi tidak valid');
}
PHP;

ob_start();
?>
<div class="card">
    <div class="card-header">
        <h3><span class="card-icon">&#128300;</span> Kalkulator</h3>
        <div style="display:flex;gap:10px;align-items:center;">
            <?php echo getSecurityBadge('high'); ?>
            <?php echo getModeBadge($secure); ?>
        </div>
    </div>
    <div class="card-body">
        <?php if ($secure): ?>
            <div class="alert alert-success"><span class="alert-icon">&#10004;</span>
                <div><strong>Mode SECURE aktif.</strong> Hanya karakter matematika yang diizinkan (regex whitelist). <code><?php echo e('2+2; system(' . "'" . $whoamiCmd . "'" . ')'); ?></code> ditolak.</div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger"><span class="alert-icon">&#9888;</span>
                <div><strong>Mode VULNERABLE aktif.</strong> Input di-<code>eval()</code> mentah. Coba <code><?php echo e('2+2; system(' . "'" . $whoamiCmd . "'" . ')'); ?></code> atau <code><?php echo e('1; phpinfo()'); ?></code>.</div>
            </div>
        <?php endif; ?>

        <form method="post" action="/rce/eval.php">
            <div class="floating-label">
                <input type="text" name="expr" id="expr" placeholder="contoh: (5+3) * 2" value="<?php echo e($expr); ?>" autocomplete="off">
                <label for="expr">Ekspresi matematika</label>
            </div>
            <button type="submit" class="btn btn-primary">= Hitung</button>
        </form>
        <form method="post" action="/rce/eval.php">
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

<?php if ($expr !== '' && $result !== null): ?>
<div class="card">
    <div class="card-header">
        <h3><span class="card-icon">&#128187;</span> Hasil</h3>
        <span class="badge <?php echo $secure ? 'badge-success' : 'badge-danger'; ?>">
            <?php echo $secure ? 'Sanitized' : 'eval() Raw'; ?>
        </span>
    </div>
    <div class="card-body">
        <div class="terminal">
            <div class="terminal-header"><span class="terminal-dot" style="background:#ff4757;"></span><span class="terminal-dot" style="background:#ffa502;"></span><span class="terminal-dot" style="background:#00ff88;"></span></div>
            <span class="text-muted">&gt;</span> <?php echo e($expr); ?><br><br>
            <strong>= <?php echo e(var_export($result, true)); ?></strong><br>
            <?php if (!$secure): ?>
            <span class="small" style="color:var(--warning);">[mode vulnerable] output eksekusi sistem mungkin tercetak di atas</span>
            <?php endif; ?>
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