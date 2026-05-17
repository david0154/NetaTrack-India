<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'NetaTrack India Admin') ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars($assetBase ?? '/php/public/assets') ?>/css/admin.css">
</head>
<body class="admin-body">
<div class="admin-shell">
    <aside class="sidebar">
        <div class="sidebar__brand">
            <div class="logo-dot"></div>
            <div>
                <strong>NetaTrack</strong>
                <span>Admin Panel</span>
            </div>
        </div>

        <nav class="sidebar__nav">
            <a href="/admin">Dashboard</a>
            <a href="/admin/leaders">Leaders</a>
            <a href="/admin/promises">Promises</a>
            <a href="/admin/projects">Projects</a>
            <a href="/admin/reports">Public Reports</a>
            <a href="/admin/scraper">AI Scraper</a>
            <a href="/admin/users">Users</a>
            <a href="/admin/analytics">Analytics</a>
            <a href="/admin/settings">Settings</a>
            <a href="/logout">Logout</a>
        </nav>
    </aside>

    <main class="main-panel">
        <header class="topbar">
            <div>
                <h1><?= htmlspecialchars($title ?? 'Dashboard') ?></h1>
                <p>Political transparency operations console</p>
            </div>
            <div class="topbar__meta">
                <span class="badge badge--success"><?= htmlspecialchars($_SESSION['user_role'] ?? 'guest') ?></span>
                <span><?= htmlspecialchars($_SESSION['user_name'] ?? 'Guest') ?></span>
            </div>
        </header>

        <?php foreach (($_SESSION['flash']['success'] ?? []) as $message): ?>
            <div class="alert alert--success"><?= htmlspecialchars($message) ?></div>
        <?php endforeach; unset($_SESSION['flash']['success']); ?>

        <?php foreach (($_SESSION['flash']['error'] ?? []) as $message): ?>
            <div class="alert alert--danger"><?= htmlspecialchars($message) ?></div>
        <?php endforeach; unset($_SESSION['flash']['error']); ?>

        <section class="content-area">
            <?= $content ?>
        </section>
    </main>
</div>
<script src="<?= htmlspecialchars($assetBase ?? '/php/public/assets') ?>/js/admin.js"></script>
</body>
</html>
