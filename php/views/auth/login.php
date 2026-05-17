<?php $page_title = 'Login'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — NetaTrack India</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{background:#0f172a;color:#e2e8f0;font-family:Inter,sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1rem}
.auth-card{background:rgba(30,41,59,.8);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.08);border-radius:20px;padding:2.5rem;width:100%;max-width:420px;box-shadow:0 25px 50px rgba(0,0,0,.5)}
.auth-brand{text-align:center;margin-bottom:2rem}
.auth-brand .flag{font-size:2.5rem}
.auth-brand h1{font-size:1.5rem;font-weight:700;color:#f8fafc;margin-top:.5rem}
.auth-brand p{color:#64748b;font-size:.875rem;margin-top:.25rem}
.form-group{margin-bottom:1.25rem}
.form-group label{display:block;font-size:.8rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.5rem}
.form-group input{width:100%;background:rgba(15,23,42,.8);border:1.5px solid rgba(255,255,255,.1);border-radius:10px;padding:.75rem 1rem;color:#f1f5f9;font-size:.95rem;font-family:inherit;transition:.2s}
.form-group input:focus{outline:none;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.15)}
.btn-primary{width:100%;padding:.85rem;background:linear-gradient(135deg,#3b82f6,#2563eb);border:none;border-radius:10px;color:#fff;font-size:1rem;font-weight:600;cursor:pointer;transition:.2s;font-family:inherit}
.btn-primary:hover{transform:translateY(-1px);box-shadow:0 8px 20px rgba(59,130,246,.4)}
.auth-footer{text-align:center;margin-top:1.5rem;color:#64748b;font-size:.875rem}
.auth-footer a{color:#3b82f6;text-decoration:none;font-weight:500}
.flash{padding:.75rem 1rem;border-radius:10px;margin-bottom:1.25rem;font-size:.875rem;font-weight:500}
.flash-error{background:rgba(239,68,68,.15);color:#fca5a5;border:1px solid rgba(239,68,68,.3)}
.flash-success{background:rgba(34,197,94,.15);color:#86efac;border:1px solid rgba(34,197,94,.3)}
.divider{display:flex;align-items:center;gap:.75rem;margin:1.25rem 0;color:#475569;font-size:.8rem}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:rgba(255,255,255,.08)}
.btn-admin{display:block;text-align:center;padding:.75rem;background:rgba(249,115,22,.1);border:1.5px solid rgba(249,115,22,.3);border-radius:10px;color:#fb923c;font-size:.875rem;font-weight:600;text-decoration:none;transition:.2s}
.btn-admin:hover{background:rgba(249,115,22,.2)}
</style>
</head>
<body>
<div class="auth-card">
    <div class="auth-brand">
        <div class="flag">🇮🇳</div>
        <h1>NetaTrack India</h1>
        <p>Sign in to your account</p>
    </div>

    <?php if ($msg = flash('error')): ?><div class="flash flash-error"><?= e($msg) ?></div><?php endif; ?>
    <?php if ($msg = flash('success')): ?><div class="flash flash-success"><?= e($msg) ?></div><?php endif; ?>

    <form method="POST" action="<?= url('auth/login') ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" value="<?= old('email') ?>" required autofocus placeholder="you@email.com">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="••••••••">
        </div>
        <button type="submit" class="btn-primary">🔓 Sign In</button>
    </form>

    <div class="divider">or</div>
    <a href="<?= url('admin/auth/login') ?>" class="btn-admin">🛡️ Admin Login</a>

    <div class="auth-footer">
        Don&apos;t have an account? <a href="<?= url('auth/register') ?>">Register</a>
        &nbsp;&bull;&nbsp;
        <a href="<?= url('') ?>">View Site</a>
    </div>
</div>
</body>
</html>
