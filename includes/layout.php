<?php
$currentPage = isset($currentPage) ? $currentPage : basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo isset($pageTitle) ? e($pageTitle) . ' | ' : ''; ?><?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
</head>
<body>
<div class="layout">
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon">&#9881;</div>
            <div class="brand-text">
                <h1>Security Lab</h1>
                <small>XSS &rarr; RCE</small>
            </div>
        </div>

        <div class="nav-section">Menu</div>
        <a href="/index.php" class="nav-item <?php echo $currentPage === 'index.php' ? 'active' : ''; ?>">
            <span class="nav-icon">&#8962;</span> Dashboard
        </a>

        <div class="nav-section">Cross-Site Scripting</div>
        <div class="nav-item nav-group-toggle">
            <span class="nav-icon">&#9754;</span> XSS
            <span class="nav-chevron">&#9662;</span>
        </div>
        <div class="nav-sub <?php echo (strpos($currentPage, 'reflected') !== false || strpos($currentPage, 'stored') !== false || strpos($currentPage, 'dom') !== false) ? 'open' : ''; ?>">
            <a href="/xss/reflected.php" class="nav-item <?php echo strpos($currentPage, 'reflected') !== false ? 'active' : ''; ?>">&#8594; Reflected</a>
            <a href="/xss/stored.php" class="nav-item <?php echo strpos($currentPage, 'stored') !== false ? 'active' : ''; ?>">&#8594; Stored</a>
            <a href="/xss/dom.php" class="nav-item <?php echo strpos($currentPage, 'dom.php') !== false ? 'active' : ''; ?>">&#8594; DOM-Based</a>
        </div>

        <div class="nav-section">RCE</div>
        <div class="nav-item nav-group-toggle">
            <span class="nav-icon">&#9889;</span> Remote Code Exec
            <span class="nav-chevron">&#9662;</span>
        </div>
        <div class="nav-sub <?php echo (strpos($currentPage, 'upload') !== false || strpos($currentPage, 'cmd') !== false || strpos($currentPage, 'eval') !== false) ? 'open' : ''; ?>">
            <a href="/rce/upload.php" class="nav-item <?php echo strpos($currentPage, 'upload') !== false ? 'active' : ''; ?>">&#8594; File Upload</a>
            <a href="/rce/cmd.php" class="nav-item <?php echo strpos($currentPage, 'cmd') !== false ? 'active' : ''; ?>">&#8594; Command Inject</a>
            <a href="/rce/eval.php" class="nav-item <?php echo isset($currentEval) ? 'active' : ''; ?>">&#8594; Eval Inject</a>
        </div>

        <div class="nav-section">Tools</div>
        <a href="/steal-cookie.php" class="nav-item <?php echo strpos($currentPage, 'steal') !== false ? 'active' : ''; ?>">
            <span class="nav-icon">&#127891;</span> Cookie Stealer
        </a>
        <a href="/challenge.php" class="nav-item <?php echo strpos($currentPage, 'challenge') !== false ? 'active' : ''; ?>">
            <span class="nav-icon">&#127919;</span> Challenge Mode
        </a>

        <button class="sidebar-toggle-btn" type="button">&#9776; Collapse</button>
    </aside>

    <main class="main">
        <header class="header">
            <div class="header-title">
                <h2><?php echo isset($pageTitle) ? e($pageTitle) : ''; ?></h2>
                <p><?php echo isset($pageSubtitle) ? e($pageSubtitle) : 'Web Security Learning Environment'; ?></p>
            </div>
            <div class="header-actions">
                <a href="/dashboard.php" class="btn btn-outline">Dashboard</a>
                <a href="/reset-lab.php" class="btn btn-danger">&#8635; Reset Lab</a>
            </div>
        </header>

        <div class="content">
            <?php echo renderWarningBanner(); ?>
            <?php echo $content; ?>
        </div>

        <footer class="footer">
            <p><strong><?php echo APP_NAME; ?></strong> v<?php echo APP_VERSION; ?> &mdash; Ethical Hacking Lab &bull; Localhost only &bull; <a href="/docs/panduan.md" target="_blank">Panduan</a></p>
        </footer>
    </main>
</div>
<script src="/assets/js/main.js"></script>
</body>
</html>
