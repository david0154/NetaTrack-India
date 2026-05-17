<?php $page_title = 'Register — NetaTrack India'; ?>
<div class="auth-container">
  <div class="auth-card">
    <div class="auth-logo">
      <div class="logo-icon">NT</div>
      <h1>NetaTrack India<span>Create Your Account</span></h1>
    </div>
    <div class="auth-title">
      <h2>Join the Movement</h2>
      <p>Help track political accountability across India</p>
    </div>

    <?php if($errors = flash('errors')): ?>
      <?php foreach((array)$errors as $err): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($err) ?></div>
      <?php endforeach; ?>
    <?php endif; ?>
    <?php if($msg = flash('error')): ?>
      <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= e($msg) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= url('auth/register') ?>">
      <?= csrf_field() ?>
      <div class="form-group">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-control" placeholder="Your Name"
          value="<?= e(old('name')) ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" placeholder="you@example.com"
          value="<?= e(old('email')) ?>" required>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="Min. 8 characters" required minlength="8">
      </div>
      <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:1rem">
        By registering, you agree to report only verified, factual information.
      </div>
      <button type="submit" class="btn-auth">Create Account</button>
    </form>

    <div class="auth-footer" style="margin-top:1rem">
      Already have an account? <a href="<?= url('auth/login') ?>">Sign In</a>
    </div>
  </div>
</div>
