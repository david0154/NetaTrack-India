<?php
/**
 * NetaTrack India — Admin Layout
 */
$siteName = (new \NetaTrack\Models\Setting())->get('site_name','NetaTrack India');
$user     = auth()->user();
$pageTitle= $page_title ?? 'Admin Panel';
$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
function isActive(string $path, string $uri): string {
    return str_starts_with($uri, $path) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= e($pageTitle) ?></title>
<link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" defer></script>
</head>
<body>
<div class="admin-wrapper">

  <!-- SIDEBAR -->
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-logo">
      <div class="logo-icon">NT</div>
      <div class="logo-text"><?= e($siteName) ?><span>Admin Panel</span></div>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-section-label">Overview</div>
      <a href="<?= url('admin') ?>" class="nav-item <?= isActive('/admin', $currentUri) && $currentUri==='/admin'||$currentUri==='/admin/' ? 'active':'' ?>">
        <span class="nav-icon"><i class="fas fa-tachometer-alt"></i></span>
        <span class="nav-label">Dashboard</span>
      </a>
      <a href="<?= url('admin/analytics') ?>" class="nav-item <?= isActive('/admin/analytics',$currentUri) ?>">
        <span class="nav-icon"><i class="fas fa-chart-line"></i></span>
        <span class="nav-label">Analytics</span>
      </a>

      <div class="nav-section-label">Content</div>
      <a href="<?= url('admin/leaders') ?>" class="nav-item <?= isActive('/admin/leaders',$currentUri) ?>">
        <span class="nav-icon"><i class="fas fa-user-tie"></i></span>
        <span class="nav-label">Leaders</span>
      </a>
      <a href="<?= url('admin/promises') ?>" class="nav-item <?= isActive('/admin/promises',$currentUri) ?>">
        <span class="nav-icon"><i class="fas fa-handshake"></i></span>
        <span class="nav-label">Promises</span>
      </a>
      <a href="<?= url('admin/projects') ?>" class="nav-item <?= isActive('/admin/projects',$currentUri) ?>">
        <span class="nav-icon"><i class="fas fa-project-diagram"></i></span>
        <span class="nav-label">Projects</span>
      </a>

      <div class="nav-section-label">Moderation</div>
      <a href="<?= url('admin/reports') ?>" class="nav-item <?= isActive('/admin/reports',$currentUri) ?>">
        <span class="nav-icon"><i class="fas fa-flag"></i></span>
        <span class="nav-label">Public Reports</span>
        <?php $pending = (new \NetaTrack\Models\PublicReport())->count("status='pending'"); ?>
        <?php if($pending > 0): ?>
          <span class="nav-badge"><?= $pending ?></span>
        <?php endif; ?>
      </a>
      <a href="<?= url('admin/users') ?>" class="nav-item <?= isActive('/admin/users',$currentUri) ?>">
        <span class="nav-icon"><i class="fas fa-users"></i></span>
        <span class="nav-label">Users</span>
      </a>

      <div class="nav-section-label">AI & Scraper</div>
      <a href="<?= url('admin/scraper') ?>" class="nav-item <?= isActive('/admin/scraper',$currentUri) ?>">
        <span class="nav-icon"><i class="fas fa-robot"></i></span>
        <span class="nav-label">Auto Scraper</span>
      </a>

      <div class="nav-section-label">System</div>
      <a href="<?= url('admin/settings') ?>" class="nav-item <?= isActive('/admin/settings',$currentUri) ?>">
        <span class="nav-icon"><i class="fas fa-cog"></i></span>
        <span class="nav-label">Settings</span>
      </a>
      <a href="<?= url('/') ?>" class="nav-item" target="_blank">
        <span class="nav-icon"><i class="fas fa-external-link-alt"></i></span>
        <span class="nav-label">View Site</span>
      </a>
      <a href="<?= url('admin/logout') ?>" class="nav-item" style="--stat-color:var(--color-danger)">
        <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span>
        <span class="nav-label">Logout</span>
      </a>
    </nav>

    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="user-avatar"><?= strtoupper(substr($user['name']??'A',0,1)) ?></div>
        <div class="user-info">
          <div class="user-name"><?= e($user['name']??'Admin') ?></div>
          <div class="user-role"><?= e(str_replace('_',' ',ucfirst($user['role']??'admin'))) ?></div>
        </div>
      </div>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="admin-main" id="adminMain">
    <!-- TOPBAR -->
    <header class="admin-topbar">
      <div class="topbar-left">
        <button class="topbar-toggle" id="sidebarToggle" title="Toggle Sidebar">
          <i class="fas fa-bars"></i>
        </button>
        <div class="topbar-search">
          <span class="search-icon"><i class="fas fa-search"></i></span>
          <input type="text" placeholder="Search leaders, projects..." id="topbarSearch">
        </div>
      </div>
      <div class="topbar-right">
        <button class="topbar-btn" title="Notifications">
          <i class="fas fa-bell"></i>
          <?php if(($reportStats['pending']??0) > 0): ?>
            <span class="badge"><?= min($reportStats['pending']??0,9) ?></span>
          <?php endif; ?>
        </button>
        <button class="topbar-btn" title="Refresh Data" onclick="location.reload()">
          <i class="fas fa-sync-alt"></i>
        </button>
        <div class="topbar-profile">
          <div class="profile-avatar"><?= strtoupper(substr($user['name']??'A',0,1)) ?></div>
          <span><?= e(explode(' ',$user['name']??'Admin')[0]) ?></span>
          <i class="fas fa-chevron-down" style="font-size:.7rem;color:var(--text-muted)"></i>
        </div>
      </div>
    </header>

    <!-- Flash Messages -->
    <div style="padding:1rem 1.5rem 0">
      <?php if ($msg = flash('success')): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= e($msg) ?></div>
      <?php endif; ?>
      <?php if ($msg = flash('error')): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($msg) ?></div>
      <?php endif; ?>
      <?php if ($msg = flash('warning')): ?>
        <div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> <?= e($msg) ?></div>
      <?php endif; ?>
    </div>

    <!-- Page Content -->
    <div class="admin-content">
      <?= $content ?>
    </div>
  </main>
</div>

<script>
// Sidebar toggle
const sidebar = document.getElementById('adminSidebar');
const main    = document.getElementById('adminMain');
const toggleBtn = document.getElementById('sidebarToggle');
let collapsed = localStorage.getItem('sidebar_collapsed') === '1';

function applyCollapse() {
  if (collapsed) {
    sidebar.classList.add('collapsed');
    main.classList.add('sidebar-collapsed');
  } else {
    sidebar.classList.remove('collapsed');
    main.classList.remove('sidebar-collapsed');
  }
}
applyCollapse();

toggleBtn.addEventListener('click', () => {
  if (window.innerWidth <= 1024) {
    sidebar.classList.toggle('mobile-open');
  } else {
    collapsed = !collapsed;
    localStorage.setItem('sidebar_collapsed', collapsed ? '1' : '0');
    applyCollapse();
  }
});

// Auto-hide alerts
setTimeout(() => {
  document.querySelectorAll('.alert').forEach(a => {
    a.style.transition = 'opacity .5s';
    a.style.opacity = '0';
    setTimeout(() => a.remove(), 500);
  });
}, 4000);

// Confirm delete
document.querySelectorAll('[data-confirm]').forEach(el => {
  el.addEventListener('click', e => {
    if (!confirm(el.dataset.confirm)) e.preventDefault();
  });
});
</script>
</body>
</html>
