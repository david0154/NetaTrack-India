<?php
/**
 * NetaTrack India — 404 Page
 */
http_response_code(404);
$siteUrl = getenv('APP_URL') ?: '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>404 Not Found — NetaTrack India</title>
<link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
<div class="india-stripe"></div>
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;text-align:center;padding:32px">
  <div>
    <div style="font-size:5rem;margin-bottom:8px">🔍</div>
    <h1 style="font-size:4rem;margin-bottom:8px;color:var(--text-muted)">404</h1>
    <h2 style="margin-bottom:12px">Page Not Found</h2>
    <p style="max-width:360px;margin:0 auto 28px">The page you're looking for doesn't exist or has been moved.</p>
    <div style="display:flex;gap:12px;justify-content:center">
      <a href="/" class="btn btn-primary">Go Home</a>
      <a href="/leaders" class="btn btn-ghost">Browse Leaders</a>
    </div>
  </div>
</div>
<script src="/assets/js/app.js"></script>
</body>
</html>
