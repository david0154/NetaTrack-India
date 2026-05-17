<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'NetaTrack Auth') ?></title>
    <link rel="stylesheet" href="<?= htmlspecialchars($assetBase ?? '/php/public/assets') ?>/css/admin.css">
</head>
<body class="auth-body">
<div class="auth-shell">
    <div class="auth-card">
        <div class="auth-card__head">
            <h1><?= htmlspecialchars($title ?? 'Authentication') ?></h1>
            <p>Access the NetaTrack India control center</p>
        </div>

        <?php foreach (($_SESSION['flash']['success'] ?? []) as $message): ?>
            <div class="alert alert--success"><?= htmlspecialchars($message) ?></div>
        <?php endforeach; unset($_SESSION['flash']['success']); ?>

        <?php foreach (($_SESSION['flash']['error'] ?? []) as $message): ?>
            <div class="alert alert--danger"><?= htmlspecialchars($message) ?></div>
        <?php endforeach; unset($_SESSION['flash']['error']); ?>

        <?= $content ?>
    </div>
</div>
</body>
</html>
