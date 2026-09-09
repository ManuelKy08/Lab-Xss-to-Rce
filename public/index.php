<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$currentPage = 'index.php';
$pageTitle = 'Dashboard';
$pageSubtitle = 'Web Security Learning Environment';

if (isset($_GET['reset']) && $_GET['reset'] === '1') {
    $flash = '<div class="alert alert-success"><span class="alert-icon">&#10004;</span>
        <div><strong>Lab berhasil di-reset!</strong> Semua payload, komentar, dan log telah dikosongkan.</div></div>';
}

$stats = getStats();

ob_start();
?>
<?php if (isset($flash) && $flash): ?><?php echo $flash; ?><?php endif; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">&#128640;</div>
        <div class="stat-value"><?php echo $stats['total_payloads']; ?></div>
        <div class="stat-label">Total Payload Dieksekusi</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">&#128274;</div>
        <div class="stat-value"><?php echo $stats['total_attacks']; ?></div>
        <div class="stat-label">Serangan Terdeteksi</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">&#128172;</div>
        <div class="stat-value"><?php echo $stats['total_comments']; ?></div>
        <div class="stat-label">Total Komentar</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">&#128451;</div>
        <div class="stat-value"><?php echo $stats['total_logs']; ?></div>
        <div class="stat-label">Total Log Aktivitas</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><span class="card-icon">&#128202;</span> Ringkasan Lab</h3>
        <span class="badge badge-primary">Learning Mode</span>
    </div>
    <div class="card-body">
        <p>Selamat datang di <strong>Security Lab</strong> &mdash; lingkungan pembelajaran untuk praktisi keamanan web (white-hat). Lab ini mendemonstrasikan rantai serangan <strong>XSS to RCE</strong>.</p>
        <p style="margin-top:8px;">Setiap halaman dilengkapi dengan <strong>toggle keamanan</strong> sehingga Anda dapat membandingkan kode <em>vulnerable</em> vs <em>patched</em> secara langsung. Seluruh aktivitas hanya berjalan di localhost.</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><span class="card-icon">&#128683;</span> Informasi Session</h3>
        <span class="badge badge-primary">Active</span>
    </div>
    <div class="card-body">
        <?php $sess = getSessionStats(); ?>
        <table>
            <tbody>
                <tr><td><strong>Session ID</strong></td><td><code><?php echo e($sess['session_id']); ?></code></td></tr>
                <tr><td><strong>Session Name</strong></td><td><code><?php echo e($sess['session_name']); ?></code></td></tr>
                <tr><td><strong>IP Address</strong></td><td><?php echo e($sess['ip']); ?></td></tr>
                <tr><td><strong>User Agent</strong></td><td class="small"><?php echo e($sess['user_agent']); ?></td></tr>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><span class="card-icon">&#128221;</span> Log Aktivitas Terakhir</h3>
        <a href="/dashboard.php" class="btn btn-outline">Lihat Semua</a>
    </div>
    <div class="card-body">
        <?php $logs = getRecentLogs(10); ?>
        <?php if (count($logs) > 0): ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Tipe</th>
                            <th>Detail</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="small"><?php echo e($log['created_at']); ?></td>
                            <td><code><?php echo e($log['event_type']); ?></code></td>
                            <td class="small"><?php echo e(substr($log['details'], 0, 60)); ?></td>
                            <td><?php echo e($log['ip_address']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted">Belum ada aktivitas. Mulai eksplorasi dengan mengirimkan payload pertama Anda.</p>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../includes/layout.php';
