<section class="panel form-panel">
    <div class="panel__head"><h2>Website Settings</h2></div>
    <form method="post" class="form-grid">
        <input type="hidden" name="_token" value="<?= $this->csrf() ?>">
        <label><span>Site Name</span><input name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>"></label>
        <label><span>Tagline</span><input name="site_tagline" value="<?= htmlspecialchars($settings['site_tagline'] ?? '') ?>"></label>
        <label><span>Contact Email</span><input name="site_email" value="<?= htmlspecialchars($settings['site_email'] ?? '') ?>"></label>
        <label><span>Phone</span><input name="site_phone" value="<?= htmlspecialchars($settings['site_phone'] ?? '') ?>"></label>
        <label><span>Primary Color</span><input name="primary_color" value="<?= htmlspecialchars($settings['primary_color'] ?? '#6366f1') ?>"></label>
        <label><span>SMTP Host</span><input name="smtp_host" value="<?= htmlspecialchars($settings['smtp_host'] ?? '') ?>"></label>
        <label><span>SMTP Port</span><input name="smtp_port" value="<?= htmlspecialchars($settings['smtp_port'] ?? '') ?>"></label>
        <label><span>SMTP Username</span><input name="smtp_username" value="<?= htmlspecialchars($settings['smtp_username'] ?? '') ?>"></label>
        <label><span>SMTP From</span><input name="smtp_from" value="<?= htmlspecialchars($settings['smtp_from'] ?? '') ?>"></label>
        <label><span>Twitter</span><input name="social_twitter" value="<?= htmlspecialchars($settings['social_twitter'] ?? '') ?>"></label>
        <label><span>Facebook</span><input name="social_facebook" value="<?= htmlspecialchars($settings['social_facebook'] ?? '') ?>"></label>
        <label class="col-2"><span>Footer Text</span><textarea name="footer_text" rows="3"><?= htmlspecialchars($settings['footer_text'] ?? '') ?></textarea></label>
        <label class="col-2"><span>Google Analytics</span><textarea name="google_analytics" rows="4"><?= htmlspecialchars($settings['google_analytics'] ?? '') ?></textarea></label>
        <label class="col-2"><span>Meta Pixel</span><textarea name="meta_pixel" rows="4"><?= htmlspecialchars($settings['meta_pixel'] ?? '') ?></textarea></label>
        <div class="col-2"><button class="btn btn--primary" type="submit">Save Settings</button></div>
    </form>
</section>
