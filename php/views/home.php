<?php
/**
 * NetaTrack India — Home Page
 * Shown at: https://yoursite.com/
 */

// Check if installed
$envFile   = ROOT_PATH . '/.env';
$installed = file_exists($envFile);

// Try to get stats if DB is available
$stats = ['leaders'=>0,'reports'=>0,'states'=>28,'parties'=>0];
if ($installed) {
    try {
        require_once ROOT_PATH . '/bootstrap.php';
        if (function_exists('db')) {
            $pdo = db();
            $stats['leaders'] = $pdo->query("SELECT COUNT(*) FROM leaders WHERE status='active'")->fetchColumn();
            $stats['reports'] = $pdo->query("SELECT COUNT(*) FROM reports")->fetchColumn();
            $stats['parties'] = $pdo->query("SELECT COUNT(*) FROM parties")->fetchColumn();
        }
    } catch (Exception $e) {
        // DB not ready yet, show zeros
    }
}

$siteUrl = defined('ROOT_PATH') ? (getenv('APP_URL') ?: '') : '';
$siteName = getenv('APP_NAME') ?: 'NetaTrack India';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="NetaTrack India — Track Indian politicians, their performance, promises, and corruption reports in real time.">
<title>NetaTrack India — Track Indian Politicians</title>
<link rel="stylesheet" href="/assets/css/app.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
  .hero {
    text-align: center;
    padding: 80px 20px 64px;
    background: radial-gradient(ellipse at top, rgba(59,130,246,.12) 0%, transparent 65%);
  }
  .hero h1 {
    font-size: clamp(2rem,5vw,3.4rem);
    font-weight: 900;
    margin-bottom: 16px;
    background: linear-gradient(135deg, #f8fafc, #94a3b8);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }
  .hero .highlight {
    background: linear-gradient(135deg, var(--india-saffron), var(--india-green));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }
  .hero p {
    font-size: 1.15rem;
    max-width: 560px;
    margin: 0 auto 32px;
    color: var(--text-secondary);
  }
  .hero-btns { display:flex; gap:12px; justify-content:center; flex-wrap:wrap; }
  .stats-strip {
    background: var(--bg-card);
    border-top: 1px solid var(--border);
    border-bottom: 1px solid var(--border);
    padding: 24px 20px;
  }
  .stats-strip .inner {
    max-width: 900px; margin: 0 auto;
    display: grid; grid-template-columns: repeat(4,1fr);
    gap: 8px; text-align: center;
  }
  .stat-num  { font-size: 2.2rem; font-weight: 900; color: var(--text-primary); }
  .stat-desc { font-size: .82rem; color: var(--text-muted); margin-top: 2px; }
  @media(max-width:600px){ .stats-strip .inner { grid-template-columns:repeat(2,1fr); } }

  .features {
    max-width: 1100px; margin: 64px auto;
    display: grid; grid-template-columns: repeat(auto-fill,minmax(240px,1fr));
    gap: 20px; padding: 0 20px;
  }
  .feat-card {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 24px;
    transition: border-color .2s, transform .2s;
  }
  .feat-card:hover { border-color: var(--blue); transform: translateY(-3px); }
  .feat-icon { font-size: 2rem; margin-bottom: 12px; }
  .feat-title { font-weight: 700; margin-bottom: 6px; }
  .feat-desc  { font-size: .88rem; color: var(--text-muted); }

  .not-installed {
    background: rgba(245,158,11,.08);
    border: 1px solid var(--yellow);
    border-radius: var(--radius-lg);
    padding: 20px 24px;
    max-width: 560px; margin: 0 auto 32px;
    text-align: center;
  }
</style>
</head>
<body>
<div class="india-stripe"></div>

<!-- Topbar -->
<nav class="topbar">
  <div class="topbar-logo">
    <img src="/logo.png" alt="NetaTrack" onerror="this.style.display='none'">
    <span>NetaTrack India</span>
    <span class="badge badge-orange" style="margin-left:6px">Beta</span>
  </div>
  <div class="topbar-nav">
    <a href="/leaders">Leaders</a>
    <a href="/reports">Reports</a>
    <a href="/parties">Parties</a>
    <a href="/states">States</a>
  </div>
  <div style="display:flex;gap:8px;margin-left:16px">
    <?php if($installed): ?>
    <a href="/admin" class="btn btn-ghost btn-sm">🏛 Admin</a>
    <?php else: ?>
    <a href="/install.php" class="btn btn-warning btn-sm">🚀 Install</a>
    <?php endif ?>
  </div>
</nav>

<!-- Hero -->
<section class="hero">
  <?php if (!$installed): ?>
  <div class="not-installed">
    ⚠️ NetaTrack is not installed yet.
    <a href="/install.php" class="btn btn-warning btn-sm" style="margin-left:10px">🚀 Run Installer</a>
  </div>
  <?php endif ?>

  <h1>Track <span class="highlight">Indian Politicians</span><br>In Real Time</h1>
  <p>Monitor performance, promises, attendance, and corruption reports for every neta across all 28 states and 8 UTs.</p>
  <div class="hero-btns">
    <a href="/leaders" class="btn btn-primary btn-lg">👤 Browse Leaders</a>
    <a href="/reports"  class="btn btn-ghost  btn-lg">📋 Submit Report</a>
  </div>
</section>

<!-- Stats strip -->
<div class="stats-strip">
  <div class="inner">
    <div>
      <div class="stat-num"><?= number_format($stats['leaders']) ?>+</div>
      <div class="stat-desc">👤 Leaders tracked</div>
    </div>
    <div>
      <div class="stat-num"><?= number_format($stats['reports']) ?></div>
      <div class="stat-desc">📋 Public reports</div>
    </div>
    <div>
      <div class="stat-num">36</div>
      <div class="stat-desc">🗺️ States &amp; UTs</div>
    </div>
    <div>
      <div class="stat-num"><?= number_format($stats['parties']) ?></div>
      <div class="stat-desc">🏷️ Parties indexed</div>
    </div>
  </div>
</div>

<!-- Features -->
<div class="features">
  <?php
  $feats = [
    ['📊','Live Score','Every leader gets a real-time score based on attendance, promises kept, and public reports.'],
    ['🤖','AI Analysis','Gemini AI auto-generates unbiased summaries and sentiment analysis from news data.'],
    ['📋','Public Reports','Citizens can submit corruption reports, broken promises, and positive achievements.'],
    ['🗣️','All Languages','Supports Hindi, Bengali, Tamil, Telugu and all major Indian languages via Sarvam AI.'],
    ['🇮🇳','All 36 States','Complete coverage — every state, UT, constituency, and ward level.'],
    ['🔔','Push Notifications','WordPress / React sites get real-time updates via the REST Push API.'],
  ];
  foreach ($feats as [$icon,$title,$desc]):
  ?>
  <div class="feat-card">
    <div class="feat-icon"><?= $icon ?></div>
    <div class="feat-title"><?= $title ?></div>
    <div class="feat-desc"><?= $desc ?></div>
  </div>
  <?php endforeach ?>
</div>

<!-- Footer -->
<footer style="background:var(--bg-card);border-top:1px solid var(--border);padding:28px 24px;margin-top:32px">
  <div class="container" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
    <div style="font-size:.88rem;color:var(--text-muted)">
      🇮🇳 NetaTrack India &mdash; MIT License &mdash; Built by
      <a href="https://github.com/david0154">David</a>
    </div>
    <div style="display:flex;gap:12px;font-size:.88rem">
      <a href="/install.php" class="text-muted">Installer</a>
      <a href="https://github.com/david0154/NetaTrack-India" class="text-muted">GitHub</a>
    </div>
  </div>
</footer>

<div id="toast-container"></div>
<script src="/assets/js/app.js"></script>
</body>
</html>
