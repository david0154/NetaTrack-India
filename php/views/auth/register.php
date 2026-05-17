<?php if (!empty($error ?? '')): ?><div class="alert alert--danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="post" class="form-grid auth-form">
    <input type="hidden" name="_token" value="<?= $this->csrf() ?>">
    <label><span>Name</span><input type="text" name="name" required></label>
    <label><span>Email</span><input type="email" name="email" required></label>
    <label><span>Password</span><input type="password" name="password" required></label>
    <button class="btn btn--primary" type="submit">Create Admin</button>
</form>
<p class="auth-links">Already have an account? <a href="/login">Login</a></p>
