<?php
require_once __DIR__ . '/../../includes/functions.php';

$currentPage = 'dom.php';
$pageTitle = 'XSS DOM-Based';
$pageSubtitle = 'Cross-Site Scripting - DOM (client-side only)';

$secure = isSecureMode('xss_dom');

if (isset($_POST['toggle'])) {
    toggleSetting('xss_dom');
}

$safeExample = '/xss/dom.php#hello';
$vulnExample = "/xss/dom.php#<img src=x onerror=alert(1)>";

ob_start();
?>
<div class="card">
    <div class="card-header">
        <h3><span class="card-icon">&#128682;</span> URL Hash Renderer</h3>
        <div style="display:flex;gap:10px;align-items:center;">
            <?php echo getSecurityBadge('low'); ?>
            <?php echo getModeBadge($secure); ?>
        </div>
    </div>
    <div class="card-body">
        <p>Halaman ini membaca <strong>URL fragment (hash)</strong> dan menulisnya ke DOM. Pada mode vulnerable, nilai hash ditulis langsung dengan <code>innerHTML</code> &mdash; tidak pernah menyentuh server, sehingga WAF/server-side protection tidak bisa mendeteksinya.</p>

        <?php if ($secure): ?>
            <div class="alert alert-success"><span class="alert-icon">&#10004;</span>
                <div><strong>Mode SECURE aktif.</strong> Teks ditulis dengan <code>textContent</code> &mdash; aman dari injeksi DOM.</div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger"><span class="alert-icon">&#9888;</span>
                <div><strong>Mode VULNERABLE aktif.</strong> Hash ditulis dengan <code>innerHTML</code>. Payload seperti <code>&lt;img src=x onerror=alert(1)&gt;</code> akan dieksekusi.</div>
            </div>
        <?php endif; ?>

        <div class="terminal" style="margin-bottom:14px;">
            <div class="terminal-header"><span class="terminal-dot" style="background:#ff4757;"></span><span class="terminal-dot" style="background:#ffa502;"></span><span class="terminal-dot" style="background:#00ff88;"></span></div>
            <span class="text-muted">Output di bawah (dari hash URL):</span><br>
            <div id="dom-output" style="margin-top:8px; min-height:24px;">&mdash;</div>
        </div>

        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="<?php echo e($safeExample); ?>" class="btn btn-outline">&#128279; Hash Aman</a>
            <a href="<?php echo e($vulnExample); ?>" class="btn btn-outline">&#128279; Payload Hash</a>
            <form method="post" action="/xss/dom.php">
                <input type="hidden" name="toggle" value="1">
                <button type="submit" class="btn <?php echo $secure ? 'btn-danger' : 'btn-success'; ?>">
                    <?php echo $secure ? '&#128683; Beralih ke Vulnerable' : '&#128274; Beralih ke Secure'; ?>
                </button>
            </form>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><span class="card-icon">&#128295;</span> Perbandingan Kode (JavaScript)</h3>
    </div>
    <div class="card-body flex gap-2">
        <div style="flex:1;">
        <?php echo codePreview('vulnerable', <<<'JS'
// MODE VULNERABLE - innerHTML
var hash = location.hash.substring(1);
document.getElementById('output').innerHTML = hash;
JS
, 'js'); ?>
        </div>
        <div style="flex:1;">
        <?php echo codePreview('patched', <<<'JS'
// MODE SECURE - textContent
var hash = location.hash.substring(1);
document.getElementById('output').textContent = hash;
JS
, 'js'); ?>
        </div>
    </div>
</div>

<script>
(function () {
    var isSecure = <?php echo $secure ? 'true' : 'false'; ?>;
    var hash = window.location.hash.substring(1);

    if (!hash) return;

    var output = document.getElementById('dom-output');
    if (isSecure) {
        output.textContent = decodeURIComponent(hash);
    } else {
        output.innerHTML = decodeURIComponent(hash);
        try {
            var payload = decodeURIComponent(hash);
            fetch('/dashboard.php');
            if (payload.length > 3) {
                var logger = new Image();
                logger.src = '/steal-cookie.php?c=' + encodeURIComponent(payload);
            }
        } catch (e) {}
    }
})();
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../includes/layout.php';