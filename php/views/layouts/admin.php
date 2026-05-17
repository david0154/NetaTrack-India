<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title ?? 'Admin') ?> — NetaTrack Admin</title>
    <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-body">
<div class="admin-wrapper">

    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-brand">
            <span class="brand-icon">🇮🇳</span>
            <span class="brand-text">NetaTrack</span>
            <button class="sidebar-close" onclick="toggleSidebar()">&#x2715;</button>
        </div>
        <nav class="sidebar-nav">
            <?php
            $links = [
                [url('admin'),             '📊', 'Dashboard'],
                [url('admin/leaders'),     '👤', 'Leaders'],
                [url('admin/reports'),     '📋', 'Reports'],
                [url('admin/projects'),    '🏗️', 'Projects'],
                [url('admin/users'),       '👥', 'Users'],
                [url('admin/analytics'),   '📈', 'Analytics'],
                [url('admin/scraper'),     '🤖', 'AI Scraper'],
                [url('admin/settings'),    '⚙️', 'Settings'],
            ];
            $current = $_SERVER['REQUEST_URI'];
            foreach ($links as [$href, $icon, $label]):
                $active = str_contains($current, parse_url($href, PHP_URL_PATH)) ? ' active' : '';
            ?>
            <a href="<?= $href ?>" class="nav-item<?= $active ?>">
                <span class="nav-icon"><?= $icon ?></span>
                <span class="nav-label"><?= $label ?></span>
            </a>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-footer">
            <a href="<?= url('') ?>" class="nav-item" target="_blank">
                <span class="nav-icon">🌐</span>
                <span class="nav-label">View Site</span>
            </a>
            <a href="<?= url('auth/logout') ?>" class="nav-item nav-danger">
                <span class="nav-icon">🚪</span>
                <span class="nav-label">Logout</span>
            </a>
        </div>
    </aside>

    <!-- Main -->
    <main class="admin-main">
        <header class="admin-topbar">
            <button class="topbar-menu" onclick="toggleSidebar()">&#9776;</button>
            <div class="topbar-title"><?= e($page_title ?? 'Dashboard') ?></div>
            <div class="topbar-user">
                <span class="user-badge"><?= e(auth()->user()['name'] ?? 'Admin') ?></span>
            </div>
        </header>

        <!-- Flash messages -->
        <?php foreach (['success','error','warning'] as $t): ?>
        <?php if ($msg = flash($t)): ?>
        <div class="flash flash-<?= $t ?>"><?= e($msg) ?> <span class="flash-close" onclick="this.parentElement.remove()">&#x2715;</span></div>
        <?php endif; endforeach; ?>

        <div class="admin-content">
            <?= $content ?>
        </div>
    </main>
</div>
<script>
function toggleSidebar(){
    document.getElementById('adminSidebar').classList.toggle('open');
}
setTimeout(()=>document.querySelectorAll('.flash').forEach(el=>el.remove()), 5000);
</script>
</body>
</html>
