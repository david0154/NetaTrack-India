<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'NetaTrack Auth') ?></title>
    <link rel="stylesheet" href="/php/public/assets/css/admin.css">
</head>
<body class="auth-body">
<div class="auth-shell">
    <div class="auth-card">
        <div class="auth-card__head">
            <h1><?= htmlspecialchars($title ?? 'Authentication') ?></h1>
            <p>Access the NetaTrack India control center</p>
        </div>
        <?= $content ?>
    </div>
</div>
</body>
</html>
