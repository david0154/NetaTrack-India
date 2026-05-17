<?php
/**
 * NetaTrack India — Public Site Layout
 */
$siteUrl  = rtrim(config('app.url',''), '/');
$siteName = config('app.name','NetaTrack India');
$logoUrl  = $siteUrl . '/logo.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="NetaTrack India — Track Indian politicians, monitor promises, detect corruption">
<title><?= htmlspecialchars($pageTitle ?? $siteName) ?></title>
<link rel="stylesheet" href="<?= $siteUrl ?>/assets/css/app.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
<div class="india-stripe"></div>

<!-- Topbar -->
<nav class="topbar">
  <div class="topbar-logo">
    <img src="<?= $logoUrl ?>" alt="NetaTrack"
         onerror="this.style.display='none'">
    <span><?= htmlspecialchars($siteName) ?></span>
    <span class="badge badge-orange" style="margin-left:4px">Beta</span>
  </div>
  <div class="topbar-nav">
    <a href="<?= $siteUrl ?>/leaders">Leaders</a>
    <a href="<?= $siteUrl ?>/reports">Reports</a>
    <a href="<?= $siteUrl ?>/parties">Parties</a>
    <a href="<?= $siteUrl ?>/states">States</a>
  </div>
  <div style="display:flex;gap:8px;margin-left:16px">
    <a href="<?= $siteUrl ?>/login"  class="btn btn-ghost btn-sm">Login</a>
    <a href="<?= $siteUrl ?>/register" class="btn btn-primary btn-sm">Sign Up</a>
  </div>
</nav>

<!-- Page content slot -->
<main>
