<?php
require_once __DIR__ . '/../../includes/functions.php';

$currentPage = 'reflected.php';
$pageTitle = 'XSS Reflected';
$pageSubtitle = 'Cross-Site Scripting - Reflected (Low security)';

$secure = isSecureMode('xss_reflected');

if (isset($_POST['toggle'])) {
    toggleSetting('xss_reflected');
}

$q = isset($_GET['q']) ? $_GET['q'] : '';
$result = '';

if ($q !== '') {
    if (!$secure && (stripos($q, '<script') !== false || stripos($q, '<img') !== false || stripos($q, 'onerror') !== false || stripos($q, 'onload') !== false)) {
        logPayload($q, 'xss_reflected');
    }
    $result = $secure ? e($q) : $q;
}

$vulnCode = <<<'PHP'
// MODE VULNERABLE - tanpa sanitasi
$q = $_GET['q'];
echo $q;  // reflected langsung ke halaman
PHP;

$secureCode = <<<'PHP'
// MODE SECURE - dengan sanitasi
$q = $_GET['q'];
echo htmlspecialchars($q, ENT_QUOTES, 'UTF-8');
PHP;

ob_start();
?>
<div class="card">
    <div class="card-header">
        <h3><span class="card-icon">&#128269;</span> Pencarian Rentan</h3>
        <div style="display:flex;gap:10px;align-items:center;">
            <?php echo getSecurityBadge('low'); ?>
            <?php echo getModeBadge($secure); ?>
        </div>
    </div>
    <div class="card-body">
        <p>Form pencarian ini <strong>menampilkan input pengguna tanpa sanitasi</strong>. Ketikkan payload XSS pada kolom di bawah untuk melihat bagaimana script dieksekusi di browser.</p>

        <?php if ($secure): ?>
            <div class="alert alert-success"><span class="alert-icon">&#10004;</span>
                <div><strong>Mode SECURE aktif.</strong> Input disanitasi dengan <code>htmlspecialchars()</code>. Payload akan tampil sebagai teks biasa.</div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger"><span class="alert-icon">&#9888;</span>
                <div><strong>Mode VULNERABLE aktif.</strong> Input direfleksikan mentah. Payload XSS akan dieksekusi browser.</div>
            </div>
        <?php endif; ?>

        <form method="get" action="/xss/reflected.php">
            <div class="floating-label">
                <input type="text" name="q" id="q" placeholder="Cari sesuatu..." value="<?php echo e($q); ?>">
                <label for="q">Kata kunci pencarian</label>
            </div>
            <button type="submit" class="btn btn-primary">&#128269; Cari</button>
        </form>

        <div class="mode-switch" style="margin-top:16px;margin-bottom:0;">
            <form method="post" action="/xss/reflected.php" style="margin:0;">
                <input type="hidden" name="toggle" value="1">
                <button type="submit" class="btn <?php echo $secure ? 'btn-danger' : 'btn-success'; ?>">
                    <?php echo $secure ? '&#128683; Beralih ke Vulnerable' : '&#128274; Beralih ke Secure'; ?>
                </button>
            </form>
            <span class="mode-label">Toggle mode keamanan sisi server</span>
        </div>
    </div>
</div>

<?php if ($q !== ''): ?>
<div class="card">
    <div class="card-header">
        <h3><span class="card-icon">&#128681;</span> Hasil Pencarian</h3>
        <span class="badge <?php echo $secure ? 'badge-success' : 'badge-danger'; ?>">
            <?php echo $secure ? 'Sanitized' : 'Reflected Raw'; ?>
        </span>
    </div>
    <div class="card-body">
        <div class="terminal">
            <div class="terminal-header"><span class="terminal-dot" style="background:#ff4757;"></span><span class="terminal-dot" style="background:#ffa502;"></span><span class="terminal-dot" style="background:#00ff88;"></span></div>
            <p>Hasil untuk: <strong><?php echo $result; ?></strong></p>
            <p class="small" style="opacity:.6">(Jika payload <code>&lt;script&gt;</code>, halaman ini mengeksekusinya dalam mode vulnerable)</p>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3><span class="card-icon">&#128640;</span> Payload Contoh</h3>
    </div>
    <div class="card-body">
        <table>
            <thead><tr><th>Payload</th><th>Efek</th></tr></thead>
            <tbody>
                <tr><td><code>&lt;script&gt;alert('XSS')&lt;/script&gt;</code></td><td>Popup sederhana</td></tr>
                <tr><td><code>&lt;script&gt;fetch('/steal-cookie.php?c='+document.cookie)&lt;/script&gt;</code></td><td>Curi cookie session</td></tr>
                <tr><td><code>&lt;img src=x onerror=alert(document.cookie)&gt;</code></td><td>Tanpa tag script</td></tr>
            </tbody>
        </table>
        <div class="card" style="margin-top:16px;">
            <div class="card-header">
                <h3><span class="card-icon">&#128295;</span> Perbandingan Kode</h3>
                <a href="?q=%3Cscript%3Ealert('XSS')%3C%2Fscript%3E" class="btn btn-outline">Coba Payload</a>
            </div>
            <div class="card-body flex gap-2">
                <div style="flex:1;"><?php echo codePreview('vulnerable', $vulnCode); ?></div>
                <div style="flex:1;"><?php echo codePreview('patched', $secureCode); ?></div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../includes/layout.php';