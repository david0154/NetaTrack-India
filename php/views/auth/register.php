<?php $page_title = 'Register'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register — NetaTrack India</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{background:#0f172a;color:#e2e8f0;font-family:Inter,sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1rem}
.auth-card{background:rgba(30,41,59,.8);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.08);border-radius:20px;padding:2.5rem;width:100%;max-width:440px;box-shadow:0 25px 50px rgba(0,0,0,.5)}
.auth-brand{text-align:center;margin-bottom:2rem}
.auth-brand .flag{font-size:2.5rem}
.auth-brand h1{font-size:1.5rem;font-weight:700;color:#f8fafc;margin-top:.5rem}
.auth-brand p{color:#64748b;font-size:.875rem}
.form-group{margin-bottom:1.1rem}
.form-group label{display:block;font-size:.8rem;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.4rem}
.form-group input{width:100%;background:rgba(15,23,42,.8);border:1.5px solid rgba(255,255,255,.1);border-radius:10px;padding:.75rem 1rem;color:#f1f5f9;font-size:.95rem;font-family:inherit;transition:.2s}
.form-group input:focus{outline:none;border-color:#22c55e;box-shadow:0 0 0 3px rgba(34,197,94,.15)}
.btn-primary{width:100%;padding:.85rem;background:linear-gradient(135deg,#22c55e,#16a34a);border:none;border-radius:10px;color:#fff;font-size:1rem;font-weight:600;cursor:pointer;transition:.2s;font-family:inherit}
.btn-primary:hover{transform:translateY(-1px);box-shadow:0 8px 20px rgba(34,197,94,.4)}
.auth-footer{text-align:center;margin-top:1.25rem;color:#64748b;font-size:.875rem}
.auth-footer a{color:#22c55e;text-decoration:none;font-weight:500}
.flash{padding:.75rem 1rem;border-radius:10px;margin-bottom:1rem;font-size:.875rem;font-weight:500}
.flash-error{background:rgba(239,68,68,.15);color:#fca5a5;border:1px solid rgba(239,68,68,.3)}
.hint{font-size:.75rem;color:#475569;margin-top:.3rem}
.terms{font-size:.8rem;color:#64748b;margin-top:.75rem;text-align:center}
.terms a{color:#22c55e}
</style>
</head>
<body>
<div class="auth-card">
    <div class="auth-brand">
        <div class="flag">🇮🇳</div>
        <h1>Join NetaTrack</h1>
        <p>Create your free account</p>
    </div>

    <?php if ($msg = flash('error')): ?><div class="flash flash-error"><?= e($msg) ?></div><?php endif; ?>

    <form method="POST" action="<?= url('auth/register') ?>">
        <?= csrf_field() ?>
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" value="<?= old('name') ?>" required autofocus placeholder="Rahul Sharma">
        </div>
        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" value="<?= old('email') ?>" required placeholder="you@email.com">
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required placeholder="Min 8 characters">
            <p class="hint">At least 8 characters with a mix of letters and numbers.</p>
        </div>
        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="password_confirm" required placeholder="Repeat password">
        </div>
        <button type="submit" class="btn-primary">🇮🇳 Create Account</button>
        <p class="terms">By registering you agree to our <a href="#">Terms of Service</a> &amp; <a href="#">Privacy Policy</a>.</p>
    </form>

    <div class="auth-footer">
        Already have an account? <a href="<?= url('auth/login') ?>">Sign In</a>
        &nbsp;&bull;&nbsp;<a href="<?= url('') ?>">View Site</a>
    </div>
</div>
</body>
</html>
