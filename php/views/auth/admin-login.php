<?php $page_title = 'Admin Login'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — NetaTrack India</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{background:#0f172a;color:#e2e8f0;font-family:Inter,sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1rem}
.auth-card{background:rgba(30,41,59,.9);backdrop-filter:blur(20px);border:1px solid rgba(249,115,22,.2);border-radius:20px;padding:2.5rem;width:100%;max-width:400px;box-shadow:0 25px 50px rgba(0,0,0,.6)}
.auth-brand{text-align:center;margin-bottom:2rem}
.shield{font-size:3rem}
.auth-brand h1{font-size:1.4rem;font-weight:700;color:#f8fafc;margin-top:.5rem}
.auth-brand p{color:#64748b;font-size:.85rem}
.admin-badge{display:inline-block;background:rgba(249,115,22,.15);color:#fb923c;border:1px solid rgba(249,115,22,.3);border-radius:20px;padding:.25rem .75rem;font-size:.75rem;font-weight:600;margin-top:.5rem}
.form-group{margin-bottom:1.25rem}
.form-group label{display:block;font-size:.8rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.5rem}
.form-group input{width:100%;background:rgba(15,23,42,.9);border:1.5px solid rgba(249,115,22,.2);border-radius:10px;padding:.75rem 1rem;color:#f1f5f9;font-size:.95rem;font-family:inherit;transition:.2s}
.form-group input:focus{outline:none;border-color:#f97316;box-shadow:0 0 0 3px rgba(249,115,22,.15)}
.btn-admin{width:100%;padding:.85rem;background:linear-gradient(135deg,#f97316,#ea580c);border:none;border-radius:10px;color:#fff;font-size:1rem;font-weight:600;cursor:pointer;transition:.2s;font-family:inherit}
.btn-admin:hover{transform:translateY(-1px);box-shadow:0 8px 20px rgba(249,115,22,.4)}
.auth-footer{text-align:center;margin-top:1.5rem;color:#64748b;font-size:.8rem}
.auth-footer a{color:#f97316;text-decoration:none;font-weight:500}
.flash{padding:.75rem 1rem;border-radius:10px;margin-bottom:1rem;font-size:.875rem}
.flash-error{background:rgba(239,68,68,.15);color:#fca5a5;border:1px solid rgba(239,68,68,.3)}
.security-note{text-align:center;color:#475569;font-size:.75rem;margin-top:1rem;padding-top:1rem;border-top:1px solid rgba(255,255,255,.06)}
</style>
</head>
<body>
<div class="auth-card">
    <div class="auth-brand">
        <div class="shield">🛡️</div>
        <h1>Admin Portal</h1>
        <span class="admin-badge">Restricted Access</span>
        <p style="margin-top:.5rem">NetaTrack India Admin</p>
    </div>

    <?php if ($msg = flash('error')): ?><div class="flash flash-error"><?= e($msg) ?></div><?php endif; ?>

    <form method="POST" action="<?= url('admin/auth/login') ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Admin Email</label>
            <input type="email" name="email" required autofocus placeholder="admin@netatrack.in">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="••••••••">
        </div>
        <button type="submit" class="btn-admin">🔐 Access Admin Panel</button>
    </form>

    <p class="security-note">🔒 This area is restricted to authorized personnel only.<br>All login attempts are logged.</p>
    <div class="auth-footer"><a href="<?= url('') ?>">← Back to Site</a></div>
</div>
</body>
</html>
