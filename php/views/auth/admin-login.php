<?php $page_title = 'Admin Login — NetaTrack India'; ?>
<div class="auth-container">
  <div class="auth-card">
    <div class="auth-logo">
      <div class="logo-icon">NT</div>
      <h1>NetaTrack India<span>Admin Panel</span></h1>
    </div>
    <div class="auth-title">
      <h2>Sign In</h2>
      <p>Access the administration panel</p>
    </div>

    <?php if($msg = flash('error')): ?>
      <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($msg) ?></div>
    <?php endif; ?>
    <?php if($msg = flash('success')): ?>
      <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= e($msg) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= url('admin/login') ?>">
      <?= csrf_field() ?>
      <div class="form-group">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control"
          placeholder="admin@netatrack.in"
          value="<?= e(old('email')) ?>" required autofocus>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
      </div>
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem">
        <label style="display:flex;align-items:center;gap:.5rem;font-size:.82rem;color:var(--text-secondary);cursor:pointer">
          <input type="checkbox" name="remember" value="1" style="accent-color:var(--color-primary)"> Remember me
        </label>
        <a href="<?= url('auth/forgot-password') ?>" style="font-size:.82rem;color:var(--color-primary)">Forgot password?</a>
      </div>
      <button type="submit" class="btn-auth">
        <i class="fas fa-sign-in-alt"></i> Sign In to Admin
      </button>
    </form>

    <div class="auth-footer">
      <a href="<?= url('/') ?>"><i class="fas fa-arrow-left"></i> Back to Website</a>
    </div>
  </div>
</div>
