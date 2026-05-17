<?php http_response_code(404); ?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>404</title>
    <link rel="stylesheet" href="/php/public/assets/css/admin.css">
</head>
<body class="auth-body">
    <div class="auth-card">
        <h1>404</h1>
        <p><?= htmlspecialchars($message ?? 'Page not found.', ENT_QUOTES, 'UTF-8') ?></p>
    </div>
</body>
</html>
