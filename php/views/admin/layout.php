<?php
/**
 * NetaTrack India — Admin Layout
 * Usage: include this file at top, call layout_end() at bottom
 */
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$siteUrl     = rtrim(config('app.url',''), '/');
$logoUrl     = $siteUrl . '/logo.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($pageTitle ?? 'Admin') ?> — NetaTrack India</title>
<link rel="stylesheet" href="<?= $siteUrl ?>/assets/css/app.css">
<link rel="stylesheet" href="<?= $siteUrl ?>/assets/css/admin.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
<div class="india-stripe"></div>
<div class="admin-layout">

  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      <img src="<?= $logoUrl ?>" alt="NetaTrack" width="36"
           onerror="this.style.display='none'">
      <span>NetaTrack</span>
    </div>

    <nav class="sidebar-nav">
      <div class="sidebar-section">Main</div>
      <?php
      $links = [
        ['dashboard',    '📊', 'Dashboard'],
        ['leaders',      '👤', 'Leaders'],
        ['reports',      '📋', 'Reports'],
        ['users',        '👥', 'Users'],
        ['announcements','📰', 'Announcements'],
        ['parties',      '🏷️', 'Parties'],
        ['states',       '🗺️', 'States'],
      ];
      foreach($links as [$page,$icon,$label]):
        $active = ($currentPage === $page) ? 'active' : '';
      ?>
      <a href="<?= $siteUrl ?>/admin/<?= $page ?>" class="sidebar-link <?= $active ?>">
        <span class="icon"><?= $icon ?></span> <?= $label ?>
      </a>
      <?php endforeach ?>

      <div class="sidebar-section" style="margin-top:8px">System</div>
      <a href="<?= $siteUrl ?>/admin/settings" class="sidebar-link <?= $currentPage==='settings'?'active':'' ?>">
        <span class="icon">⚙️</span> Settings
      </a>
      <a href="<?= $siteUrl ?>/admin/fetch" class="sidebar-link <?= $currentPage==='fetch'?'active':'' ?>">
        <span class="icon">🤖</span> Auto-Fetch
      </a>
      <a href="<?= $siteUrl ?>/admin/push" class="sidebar-link <?= $currentPage==='push'?'active':'' ?>">
        <span class="icon">🚀</span> Push Data
      </a>
    </nav>

    <div class="sidebar-footer">
      🟢 Online &nbsp;|&nbsp; <?= date('H:i') ?>
    </div>
  </aside>

  <!-- Main area -->
  <div class="admin-main">
    <!-- Admin topbar -->
    <header class="admin-topbar">
      <div class="admin-topbar-title">
        <?= htmlspecialchars($pageTitle ?? 'Admin Panel') ?>
      </div>
      <div style="display:flex;gap:8px;align-items:center">
        <a href="<?= $siteUrl ?>" target="_blank"
           class="btn btn-ghost btn-sm">🌐 View Site</a>
        <a href="<?= $siteUrl ?>/admin/logout"
           class="btn btn-ghost btn-sm">Sign Out</a>
      </div>
    </header>

    <!-- Page content -->
    <main class="admin-content">
