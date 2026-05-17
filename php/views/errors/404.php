<?php
$isAdmin = str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin/');
$layout = $isAdmin ? 'admin' : 'public';
$page_title = '404 Not Found';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>404 — Page Not Found | NetaTrack India</title>
<style>
  body{margin:0;background:#0f172a;color:#e2e8f0;font-family:Inter,sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;}
  .box{text-align:center;padding:2rem;}
  .code{font-size:8rem;font-weight:800;color:#3b82f6;line-height:1;}
  h2{font-size:1.5rem;margin:.5rem 0;color:#94a3b8;}
  p{color:#64748b;}
  a{display:inline-block;margin-top:1.5rem;padding:.75rem 2rem;background:#3b82f6;color:#fff;border-radius:8px;text-decoration:none;font-weight:600;}
  a:hover{background:#2563eb;}
</style>
</head>
<body>
<div class="box">
  <div class="code">404</div>
  <h2>Page Not Found</h2>
  <p>The page you are looking for doesn&apos;t exist or has been moved.</p>
  <a href="<?= url('') ?>">&#8592; Go Home</a>
</div>
</body>
</html>
