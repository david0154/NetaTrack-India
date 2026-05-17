<?php
$setting = new \NetaTrack\Models\Setting();
$siteName = $setting->get('site_name','NetaTrack India');
$siteTagline = $setting->get('site_tagline','Tracking Political Accountability');
$metaDesc = $page_meta_desc ?? $setting->get('meta_description','Track India\'s political leaders, promises, and projects.');
$pageTitle = isset($page_title) ? $page_title.' — '.$siteName : $siteName.' — '.$siteTagline;
$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
function navActive(string $path, string $uri): string {
    if($path==='/' ) return $uri==='/' ? 'active' : '';
    return str_starts_with($uri,$path) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="<?= e($metaDesc) ?>">
<meta name="theme-color" content="#080d1a">
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($metaDesc) ?>">
<meta property="og:type" content="website">
<title><?= e($pageTitle) ?></title>
<link rel="stylesheet" href="<?= asset('css/public.css') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js" defer></script>
</head>
<body>
<div class="bg-fx"></div>

<!-- NAV -->
<nav class="site-nav">
  <a href="<?= url('/') ?>" class="nav-logo">
    <div class="logo-mark">NT</div>
    <div>Neta<span>Track</span><small style="display:block;font-size:.6rem;font-weight:400;color:var(--text-muted);margin-top:-2px">India</small></div>
  </a>
  <div class="nav-links">
    <a href="<?= url('/') ?>"           class="nav-link <?= navActive('/',$currentUri) ?>">Home</a>
    <a href="<?= url('leaders') ?>"     class="nav-link <?= navActive('/leaders',$currentUri) ?>">Leaders</a>
    <a href="<?= url('promises') ?>"    class="nav-link <?= navActive('/promises',$currentUri) ?>">Promises</a>
    <a href="<?= url('projects') ?>"    class="nav-link <?= navActive('/projects',$currentUri) ?>">Projects</a>
    <a href="<?= url('corruption') ?>"  class="nav-link <?= navActive('/corruption',$currentUri) ?>">Corruption Index</a>
    <a href="<?= url('report') ?>"      class="nav-link <?= navActive('/report',$currentUri) ?>">Report</a>
  </div>
  <div class="nav-right">
    <div class="nav-search">
      <span class="si"><i class="fas fa-search"></i></span>
      <input type="text" placeholder="Search leaders..." id="navSearch"
        onkeydown="if(event.key==='Enter')location.href='<?= url('leaders') ?>?q='+this.value">
    </div>
    <?php if(auth()->check()): ?>
      <a href="<?= url('dashboard') ?>" class="btn-nav btn-nav-outline">My Account</a>
      <?php if(auth()->isAdmin()): ?>
        <a href="<?= url('admin') ?>" class="btn-nav btn-nav-primary"><i class="fas fa-shield-alt"></i> Admin</a>
      <?php endif; ?>
    <?php else: ?>
      <a href="<?= url('auth/login') ?>" class="btn-nav btn-nav-outline">Login</a>
      <a href="<?= url('report') ?>"     class="btn-nav btn-nav-primary"><i class="fas fa-flag"></i> Report</a>
    <?php endif; ?>
    <button class="nav-hamburger" id="navHamburger"><i class="fas fa-bars"></i></button>
  </div>
</nav>

<!-- Mobile Nav -->
<div id="mobileNav" style="
  display:none;position:fixed;top:68px;left:0;right:0;bottom:0;
  background:rgba(8,13,26,.97);z-index:199;
  padding:1.5rem;flex-direction:column;gap:.5rem;
  overflow-y:auto;
">
  <a href="<?= url('/') ?>"          class="nav-link" style="font-size:1rem;padding:.75rem 1rem">Home</a>
  <a href="<?= url('leaders') ?>"    class="nav-link" style="font-size:1rem;padding:.75rem 1rem">Leaders</a>
  <a href="<?= url('promises') ?>"   class="nav-link" style="font-size:1rem;padding:.75rem 1rem">Promises</a>
  <a href="<?= url('projects') ?>"   class="nav-link" style="font-size:1rem;padding:.75rem 1rem">Projects</a>
  <a href="<?= url('corruption') ?>" class="nav-link" style="font-size:1rem;padding:.75rem 1rem">Corruption Index</a>
  <a href="<?= url('report') ?>"     class="nav-link" style="font-size:1rem;padding:.75rem 1rem">Submit Report</a>
  <hr style="border-color:var(--border-glass);margin:.5rem 0">
  <a href="<?= url('auth/login') ?>" class="btn-nav btn-nav-outline" style="text-align:center;padding:.75rem">Login</a>
  <a href="<?= url('report') ?>"     class="btn-nav btn-nav-primary" style="text-align:center;padding:.75rem">Report Corruption</a>
</div>

<!-- Flash -->
<?php if($msg = flash('success')): ?>
  <div style="max-width:900px;margin:1rem auto;padding:0 1.5rem">
    <div class="alert-pub success"><i class="fas fa-check-circle"></i> <?= e($msg) ?></div>
  </div>
<?php endif; ?>
<?php if($msg = flash('error')): ?>
  <div style="max-width:900px;margin:1rem auto;padding:0 1.5rem">
    <div class="alert-pub error"><i class="fas fa-exclamation-circle"></i> <?= e($msg) ?></div>
  </div>
<?php endif; ?>

<!-- Content -->
<main style="position:relative;z-index:1">
  <?= $content ?>
</main>

<!-- FOOTER -->
<footer class="site-footer" style="position:relative;z-index:1">
  <div class="footer-inner">
    <div class="footer-brand">
      <div class="brand-logo">
        <div class="logo-mark">NT</div>
        <div>Neta<span style="color:var(--color-saffron)">Track</span> India</div>
      </div>
      <p>India's most comprehensive political accountability platform. Track leaders, verify promises, monitor projects and fight corruption — all in one place.</p>
      <div class="footer-social">
        <a href="#" class="social-btn"><i class="fab fa-twitter"></i></a>
        <a href="#" class="social-btn"><i class="fab fa-facebook"></i></a>
        <a href="#" class="social-btn"><i class="fab fa-instagram"></i></a>
        <a href="#" class="social-btn"><i class="fab fa-youtube"></i></a>
      </div>
    </div>
    <div class="footer-col">
      <h4>Platform</h4>
      <ul>
        <li><a href="<?= url('leaders') ?>">Leader Profiles</a></li>
        <li><a href="<?= url('promises') ?>">Promise Tracker</a></li>
        <li><a href="<?= url('projects') ?>">Project Monitor</a></li>
        <li><a href="<?= url('corruption') ?>">Corruption Index</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Participate</h4>
      <ul>
        <li><a href="<?= url('report') ?>">Submit Report</a></li>
        <li><a href="<?= url('auth/register') ?>">Join NetaTrack</a></li>
        <li><a href="<?= url('auth/login') ?>">Sign In</a></li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Legal</h4>
      <ul>
        <li><a href="#">Privacy Policy</a></li>
        <li><a href="#">Terms of Use</a></li>
        <li><a href="#">Disclaimer</a></li>
        <li><a href="#">Contact</a></li>
      </ul>
    </div>
  </div>
  <div class="footer-bottom container">
    <p>© <?= date('Y') ?> <?= e($siteName) ?>. Built for a transparent India. 🇮🇳</p>
    <div class="india-flag-strip">
      <div class="flag-dot" style="background:#f97316"></div>
      <div class="flag-dot" style="background:#fff"></div>
      <div class="flag-dot" style="background:#138808"></div>
    </div>
  </div>
</footer>

<script>
// Mobile nav
const hamburger = document.getElementById('navHamburger');
const mobileNav = document.getElementById('mobileNav');
hamburger?.addEventListener('click',()=>{
  const open = mobileNav.style.display==='flex';
  mobileNav.style.display = open ? 'none' : 'flex';
});

// Auto-dismiss flash
setTimeout(()=>{
  document.querySelectorAll('.alert-pub').forEach(a=>{
    a.style.transition='opacity .5s';
    a.style.opacity='0';
    setTimeout(()=>a.remove(),500);
  });
},4500);
</script>
</body>
</html>
