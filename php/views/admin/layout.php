<?php
/**
 * NetaTrack India — PHP Admin Layout with logo
 */
$logo_url = e($settings['site_logo'] ?? '');
$site_name = e($settings['site_name'] ?? 'NetaTrack India');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $page_title ?? 'Admin' ?> — <?= $site_name ?> Admin</title>
    <link rel="icon" type="image/png" href="<?= url('logo.png') ?>">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg:       #0f172a;
            --surface:  #1e293b;
            --border:   #334155;
            --text:     #f8fafc;
            --muted:    #94a3b8;
            --accent:   #3b82f6;
            --green:    #22c55e;
            --red:      #ef4444;
            --yellow:   #f59e0b;
        }
        body { background: var(--bg); color: var(--text); font-family: 'Segoe UI', sans-serif; min-height: 100vh; }

        /* Topbar */
        .admin-topbar {
            display: flex; align-items: center; gap: 12px;
            background: var(--surface); height: 56px;
            padding: 0 20px; border-bottom: 1px solid var(--border);
            position: sticky; top: 0; z-index: 100;
        }
        .admin-topbar .logo-wrap img {
            height: 36px; width: 36px; object-fit: contain;
            border-radius: 6px;
        }
        .admin-topbar .logo-wrap .logo-fallback {
            width: 36px; height: 36px;
            background: var(--accent); border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; font-weight: bold; color: #fff;
        }
        .admin-topbar .site-title {
            font-size: 1rem; font-weight: 700; color: var(--text);
        }
        .admin-topbar .flag { font-size: 1.3rem; }
        .admin-topbar .spacer { flex: 1; }
        .admin-topbar .btn-sm {
            padding: 6px 14px; border-radius: 6px; font-size: .85rem;
            border: none; cursor: pointer; text-decoration: none;
        }
        .admin-topbar .btn-logout { background: rgba(239,68,68,.15); color: var(--red); }
        .admin-topbar .btn-site   { background: rgba(34,197,94,.15); color: var(--green); }

        /* Layout */
        .admin-body { display: flex; min-height: calc(100vh - 56px); }

        /* Sidebar */
        .admin-sidebar {
            width: 220px; min-width: 220px;
            background: var(--surface);
            border-right: 1px solid var(--border);
            padding: 16px 0;
        }
        .admin-sidebar .nav-logo {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 20px 20px;
            border-bottom: 1px solid var(--border);
            margin-bottom: 12px;
        }
        .admin-sidebar .nav-logo img {
            height: 40px; width: 40px; object-fit: contain; border-radius: 8px;
        }
        .admin-sidebar .nav-logo span {
            font-size: .9rem; font-weight: 700; color: var(--text);
            line-height: 1.2;
        }
        .admin-sidebar a {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 20px; color: var(--muted);
            text-decoration: none; font-size: .9rem;
            transition: background .15s, color .15s;
            border-left: 3px solid transparent;
        }
        .admin-sidebar a:hover,
        .admin-sidebar a.active {
            background: rgba(59,130,246,.08);
            color: var(--text);
            border-left-color: var(--accent);
        }

        /* Content */
        .admin-content { flex: 1; padding: 24px; overflow-x: auto; }

        /* Status bar */
        .admin-statusbar {
            background: #020617; color: var(--muted);
            font-size: .75rem; padding: 4px 16px;
            border-top: 1px solid var(--border);
        }
    </style>
    <?php if (isset($extra_css)) echo $extra_css; ?>
</head>
<body>

<!-- Topbar -->
<div class="admin-topbar">
    <div class="logo-wrap">
        <?php if ($logo_url): ?>
            <img src="<?= $logo_url ?>" alt="<?= $site_name ?>">
        <?php else: ?>
            <div class="logo-fallback">🇮🇳</div>
        <?php endif; ?>
    </div>
    <span class="site-title"><?= $site_name ?></span>
    <span class="flag">🇮🇳</span>
    <span class="spacer"></span>
    <a href="<?= url('/') ?>" class="btn-sm btn-site" target="_blank">🌐 View Site</a>
    <a href="<?= url('admin/logout') ?>" class="btn-sm btn-logout">🚪 Logout</a>
</div>

<div class="admin-body">
    <!-- Sidebar -->
    <nav class="admin-sidebar">
        <div class="nav-logo">
            <img src="<?= url('logo.png') ?>" alt="logo"
                 onerror="this.style.display='none'">
            <span>NetaTrack<br>Admin</span>
        </div>
        <?php
        $nav = [
            ['url' => 'admin/dashboard',      'icon' => '📊', 'label' => 'Dashboard'],
            ['url' => 'admin/leaders',        'icon' => '👤', 'label' => 'Leaders'],
            ['url' => 'admin/reports',        'icon' => '📋', 'label' => 'Reports'],
            ['url' => 'admin/users',          'icon' => '👥', 'label' => 'Users'],
            ['url' => 'admin/announcements',  'icon' => '📢', 'label' => 'Announcements'],
            ['url' => 'admin/cases',          'icon' => '⚖️',  'label' => 'Cases'],
            ['url' => 'admin/funds',          'icon' => '💰', 'label' => 'Funds'],
            ['url' => 'admin/settings',       'icon' => '⚙️',  'label' => 'Settings'],
        ];
        $current = $_SERVER['REQUEST_URI'] ?? '';
        foreach ($nav as $item):
            $active = str_contains($current, $item['url']) ? 'active' : '';
        ?>
        <a href="<?= url($item['url']) ?>" class="<?= $active ?>">
            <span><?= $item['icon'] ?></span>
            <?= $item['label'] ?>
        </a>
        <?php endforeach; ?>
    </nav>

    <!-- Main content -->
    <main class="admin-content">
        <?php echo $content ?? ''; ?>
    </main>
</div>

<!-- Status bar -->
<div class="admin-statusbar">
    🟢 Connected — NetaTrack India Admin &nbsp;|
    AI: <?= implode(', ', ai()->activeProviders()) ?: 'No key configured' ?>
</div>

<?php if (isset($extra_js)) echo $extra_js; ?>
</body>
</html>
