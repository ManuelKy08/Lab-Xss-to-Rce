<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$currentPage = 'dashboard.php';
$pageTitle = 'Dashboard';
$pageSubtitle = 'Statistik, Log &amp; Session';

$stats = getStats();
$logs = getRecentLogs(30);
$cookies = getCookieLogs(20);
$raw = rawRequestDump();

ob_start();
?>
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
        <div class="stat-icon">&#128374;</div>
        <div class="stat-value"><?php echo count($cookies); ?></div>
        <div class="stat-label">Cookie Tercuri</div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><span class="card-icon">&#128221;</span> Log Aktivitas</h3>
        <span class="badge badge-primary"><?php echo count($logs); ?> entries</span>
    </div>
    <div class="card-body">
        <?php if (count($logs) > 0): ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Tipe</th>
                            <th>Detail</th>
                            <th>IP</th>
                            <th>User Agent</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                        <tr>
                            <td class="small"><?php echo e($log['created_at']); ?></td>
                            <td><code><?php echo e($log['event_type']); ?></code></td>
                            <td class="small"><?php echo e(substr($log['details'], 0, 80)); ?></td>
                            <td><?php echo e($log['ip_address']); ?></td>
                            <td class="small"><?php echo e(substr($log['user_agent'], 0, 40)); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted">Belum ada log aktivitas.</p>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><span class="card-icon">&#127891;</span> Cookie yang Ditangkap</h3>
        <span class="badge <?php echo count($cookies) ? 'badge-danger' : 'badge-primary'; ?>">
            <?php echo count($cookies) ? 'Stolen!' : 'Empty'; ?>
        </span>
    </div>
    <div class="card-body">
        <?php if (count($cookies) > 0): ?>
            <ul style="list-style:none;">
                <?php foreach ($cookies as $line): ?>
                <li style="padding:8px 0;border-bottom:1px solid var(--border);font-family:'Fira Code',monospace;font-size:12px;color:var(--warning);word-break:break-all;">
                    <?php echo e($line); ?>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <?php echo renderHeader(['status' => 'No cookies captured yet']); ?>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><span class="card-icon">&#128208;</span> Raw Request (untuk Burp Suite Repeater)</h3>
        <button class="btn btn-outline" data-copy="#raw-request">Copy Raw</button>
    </div>
    <div class="card-body">
        <div class="terminal" id="raw-request"><?php echo e($raw); ?></div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../includes/layout.php';