<?php $page_title = 'Login — NetaTrack India'; ?>
<div class="auth-container">
  <div class="auth-card">
    <div class="auth-logo">
      <div class="logo-icon">NT</div>
      <h1>NetaTrack India<span>Political Transparency Platform</span></h1>
    </div>
    <div class="auth-title">
      <h2>Welcome Back</h2>
      <p>Log in to track and report political accountability</p>
    </div>

    <?php if($msg = flash('error')): ?>
      <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($msg) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= url('auth/login') ?>">
      <?= csrf_field() ?>
      <div class="form-group">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" placeholder="you@example.com" required autofocus>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn-auth">Sign In</button>
    </form>

    <div class="divider">or</div>

    <div class="auth-footer">
      Don’t have an account? <a href="<?= url('auth/register') ?>">Register</a>
    </div>
    <div class="auth-footer" style="margin-top:.5rem">
      <a href="<?= url('/') ?>"><i class="fas fa-arrow-left"></i> Back to Website</a>
    </div>
  </div>
</div>
