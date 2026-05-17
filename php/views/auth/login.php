<?php if (!empty($error ?? '')): ?><div class="alert alert--danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="post" class="form-grid auth-form">
    <input type="hidden" name="_token" value="<?= $this->csrf() ?>">
    <label><span>Email</span><input type="email" name="email" required></label>
    <label><span>Password</span><input type="password" name="password" required></label>
    <button class="btn btn--primary" type="submit">Login</button>
</form>
<p class="auth-links">No admin account yet? <a href="/register">Create one</a></p>
