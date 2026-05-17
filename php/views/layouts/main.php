<?php
$siteName = (new \NetaTrack\Models\Setting())->get('site_name','NetaTrack India');
$currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
function navActive(string $path, string $uri): string {
    return $uri === $path || ($path !== '/' && str_starts_with($uri, $path)) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="<?= e($meta_description ?? 'NetaTrack India — Track political leaders, promises, projects and corruption across India.') ?>">
<meta name="theme-color" content="#070d1a">
<title><?= e($page_title ?? $siteName) ?></title>
<!-- OG Tags -->
<meta property="og:title" content="<?= e($page_title ?? $siteName) ?>">
<meta property="og:description" content="India\'s #1 political transparency platform">
<meta property="og:type" content="website">
<link rel="stylesheet" href="<?= asset('css/public.css') ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
  <a href="<?= url('/') ?>" class="nav-brand">
    <div class="brand-icon">NT</div>
    <span><?= e($siteName) ?></span>
    <div class="tricolor"><span class="t-saffron"></span><span class="t-white"></span><span class="t-green"></span></div>
  </a>

  <div class="nav-links">
    <a href="<?= url('/') ?>"          class="nav-link <?= navActive('/',$currentUri) ?>">Home</a>
    <a href="<?= url('leaders') ?>"    class="nav-link <?= navActive('/leaders',$currentUri) ?>">Leaders</a>
    <a href="<?= url('promises') ?>"   class="nav-link <?= navActive('/promises',$currentUri) ?>">Promises</a>
    <a href="<?= url('projects') ?>"   class="nav-link <?= navActive('/projects',$currentUri) ?>">Projects</a>
    <a href="<?= url('states') ?>"     class="nav-link <?= navActive('/states',$currentUri) ?>">States</a>
    <a href="<?= url('corruption') ?>" class="nav-link <?= navActive('/corruption',$currentUri) ?>">Corruption Index</a>
  </div>

  <div class="nav-right">
    <div class="nav-search">
      <span class="si"><i class="fas fa-search"></i></span>
      <input type="text" placeholder="Search leaders..." id="navSearchInput"
        onkeydown="if(event.key==='Enter'){window.location='<?= url('search') ?>?q='+encodeURIComponent(this.value)}">
    </div>
    <?php if(auth()->check()): ?>
      <a href="<?= url('/') ?>" class="btn-nav btn-nav-ghost"><?= e(explode(' ',auth()->user()['name'])[0]) ?></a>
      <?php if(auth()->isAdmin()): ?>
        <a href="<?= url('admin') ?>" class="btn-nav btn-nav-primary">Admin</a>
      <?php endif; ?>
    <?php else: ?>
      <a href="<?= url('auth/login') ?>"    class="btn-nav btn-nav-ghost">Login</a>
      <a href="<?= url('auth/register') ?>" class="btn-nav btn-nav-primary">Join Free</a>
    <?php endif; ?>
    <button class="hamburger" id="hamburger" aria-label="Menu"><i class="fas fa-bars"></i></button>
  </div>
</nav>

<!-- MOBILE MENU -->
<div class="mobile-menu" id="mobileMenu">
  <button onclick="closeMobileMenu()" style="position:absolute;top:1.25rem;right:1.25rem;background:var(--glass);border:1px solid var(--border);color:var(--t2);width:38px;height:38px;border-radius:var(--r-sm);cursor:pointer;display:flex;align-items:center;justify-content:center;">
    <i class="fas fa-times"></i>
  </button>
  <a href="<?= url('/') ?>"          class="nav-link" style="font-size:1.1rem;padding:.75rem 1rem">Home</a>
  <a href="<?= url('leaders') ?>"    class="nav-link" style="font-size:1.1rem;padding:.75rem 1rem">Leaders</a>
  <a href="<?= url('promises') ?>"   class="nav-link" style="font-size:1.1rem;padding:.75rem 1rem">Promises</a>
  <a href="<?= url('projects') ?>"   class="nav-link" style="font-size:1.1rem;padding:.75rem 1rem">Projects</a>
  <a href="<?= url('states') ?>"     class="nav-link" style="font-size:1.1rem;padding:.75rem 1rem">States</a>
  <a href="<?= url('corruption') ?>" class="nav-link" style="font-size:1.1rem;padding:.75rem 1rem">Corruption Index</a>
  <a href="<?= url('submit-report') ?>" class="btn btn-primary" style="margin-top:1rem"><i class="fas fa-flag"></i> Submit Report</a>
</div>

<!-- Flash -->
<?php if($msg = flash('success')): ?>
<div style="position:fixed;bottom:1.5rem;right:1.5rem;z-index:2000" class="alert alert-success animate-fade">
  <i class="fas fa-check-circle"></i> <?= e($msg) ?>
</div>
<?php endif; ?>

<!-- Content -->
<?= $content ?>

<!-- FOOTER -->
<footer class="footer">
  <div class="tricolor-bar"></div>
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <div class="nav-brand" style="margin-bottom:.75rem">
          <div class="brand-icon" style="width:32px;height:32px;font-size:.8rem">NT</div>
          <span class="brand-name"><?= e($siteName) ?></span>
        </div>
        <p>India\'s most comprehensive political transparency platform. Track leaders, promises, projects and hold representatives accountable.</p>
      </div>
      <div>
        <div class="footer-heading">Platform</div>
        <div class="footer-links">
          <a href="<?= url('leaders') ?>"    class="footer-link">Leader Profiles</a>
          <a href="<?= url('promises') ?>"   class="footer-link">Promise Tracker</a>
          <a href="<?= url('projects') ?>"   class="footer-link">Project Monitor</a>
          <a href="<?= url('states') ?>"     class="footer-link">State Reports</a>
          <a href="<?= url('corruption') ?>" class="footer-link">Corruption Index</a>
        </div>
      </div>
      <div>
        <div class="footer-heading">Participate</div>
        <div class="footer-links">
          <a href="<?= url('submit-report') ?>" class="footer-link">Submit Report</a>
          <a href="<?= url('auth/register') ?>"  class="footer-link">Join Community</a>
          <a href="<?= url('search') ?>"          class="footer-link">Search</a>
        </div>
      </div>
      <div>
        <div class="footer-heading">Legal</div>
        <div class="footer-links">
          <a href="#" class="footer-link">Privacy Policy</a>
          <a href="#" class="footer-link">Terms of Use</a>
          <a href="#" class="footer-link">Disclaimer</a>
          <a href="#" class="footer-link">Contact Us</a>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <span>&copy; <?= date('Y') ?> <?= e($siteName) ?>. Built for Democracy.</span>
      <span>Made with <span style="color:var(--red)">♥</span> for India &nbsp;🇮🇳</span>
    </div>
  </div>
</footer>

<script>
// Mobile menu
const mobileMenu = document.getElementById('mobileMenu');
document.getElementById('hamburger').addEventListener('click', () => mobileMenu.classList.add('open'));
function closeMobileMenu() { mobileMenu.classList.remove('open'); }
mobileMenu.addEventListener('click', e => { if(e.target===mobileMenu) closeMobileMenu(); });

// Auto-hide flash
setTimeout(() => {
  document.querySelectorAll('.alert').forEach(a => {
    a.style.transition='opacity .5s';
    a.style.opacity='0';
    setTimeout(()=>a.remove(),500);
  });
}, 4000);
</script>
</body>
</html>
