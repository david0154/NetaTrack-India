<?php $page_title = 'Settings'; ?>
<div class="settings-page">
    <h2>Site Settings</h2>

    <form method="POST" action="<?= url('admin/settings') ?>" class="admin-form">
        <?= csrf_field() ?>

        <section class="form-section">
            <h3>🌐 General</h3>
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Site Name</label>
                    <input type="text" name="site_name" value="<?= e($settings['site_name'] ?? 'NetaTrack India') ?>">
                </div>
                <div class="form-group">
                    <label>Tagline</label>
                    <input type="text" name="site_tagline" value="<?= e($settings['site_tagline'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Site URL</label>
                    <input type="url" name="site_url" value="<?= e($settings['site_url'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Logo URL</label>
                    <input type="url" name="site_logo" value="<?= e($settings['site_logo'] ?? '') ?>" placeholder="https://yourdomain.com/logo.png">
                </div>
            </div>
            <div class="form-group">
                <label>Meta Description</label>
                <textarea name="meta_description" rows="2"><?= e($settings['meta_description'] ?? '') ?></textarea>
            </div>
        </section>

        <section class="form-section">
            <h3>🤖 AI Configuration</h3>
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Gemini API Key</label>
                    <input type="password" name="gemini_api_key" value="<?= e($settings['gemini_api_key'] ?? '') ?>" placeholder="AIza...">
                </div>
                <div class="form-group">
                    <label>Sarvam AI Key</label>
                    <input type="password" name="sarvam_api_key" value="<?= e($settings['sarvam_api_key'] ?? '') ?>">
                </div>
                <div class="form-group" style="display:flex;align-items:center;gap:8px">
                    <input type="checkbox" name="ai_enabled" id="ai_enabled" value="1" <?= !empty($settings['ai_enabled']) && $settings['ai_enabled']=='1' ? 'checked' : '' ?>>
                    <label for="ai_enabled" style="margin:0">Enable AI Analysis</label>
                </div>
            </div>
        </section>

        <section class="form-section">
            <h3>📊 Analytics & Ads</h3>
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Google Analytics ID</label>
                    <input type="text" name="google_analytics_id" value="<?= e($settings['google_analytics_id'] ?? '') ?>" placeholder="G-XXXXXXXXXX">
                </div>
                <div class="form-group">
                    <label>Google AdSense Publisher ID / Code</label>
                    <input type="text" name="google_adsense_code" value="<?= e($settings['google_adsense_code'] ?? '') ?>" placeholder="ca-pub-xxxxxxxxxxxxxxxx">
                </div>
            </div>
        </section>

        <section class="form-section">
            <h3>📣 Sponsor & Announcement</h3>
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Sponsor Title</label>
                    <input type="text" name="sponsor_title" value="<?= e($settings['sponsor_title'] ?? '') ?>" placeholder="Sponsored By">
                </div>
                <div class="form-group">
                    <label>Announcement Link</label>
                    <input type="url" name="announcement_link" value="<?= e($settings['announcement_link'] ?? '') ?>">
                </div>
                <div class="form-group" style="grid-column:1/-1">
                    <label>Sponsor HTML / Embed Code</label>
                    <textarea name="sponsor_html" rows="3" placeholder="<a href='...'><img src='...'></a>"><?= e($settings['sponsor_html'] ?? '') ?></textarea>
                </div>
                <div class="form-group" style="grid-column:1/-1">
                    <label>Announcement Text</label>
                    <textarea name="announcement_text" rows="2" placeholder="Breaking: New report is now live"><?= e($settings['announcement_text'] ?? '') ?></textarea>
                </div>
                <div class="form-group" style="display:flex;align-items:center;gap:8px">
                    <input type="checkbox" name="announcement_active" id="announcement_active" value="1" <?= !empty($settings['announcement_active']) && $settings['announcement_active']=='1' ? 'checked' : '' ?>>
                    <label for="announcement_active" style="margin:0">Enable Announcement Bar</label>
                </div>
            </div>
        </section>

        <section class="form-section">
            <h3>📧 SMTP Email</h3>
            <div class="form-grid-2">
                <div class="form-group"><label>SMTP Host</label><input type="text" name="smtp_host" value="<?= e($settings['smtp_host'] ?? '') ?>"></div>
                <div class="form-group"><label>SMTP Port</label><input type="number" name="smtp_port" value="<?= e($settings['smtp_port'] ?? 587) ?>"></div>
                <div class="form-group"><label>SMTP User</label><input type="text" name="smtp_user" value="<?= e($settings['smtp_user'] ?? '') ?>"></div>
                <div class="form-group"><label>SMTP Password</label><input type="password" name="smtp_pass" value="<?= e($settings['smtp_pass'] ?? '') ?>"></div>
            </div>
        </section>

        <section class="form-section">
            <h3>🔒 Access Control</h3>
            <div class="form-grid-2">
                <div class="form-group" style="display:flex;align-items:center;gap:8px">
                    <input type="checkbox" name="registration_enabled" id="reg" value="1" <?= ($settings['registration_enabled'] ?? '1')=='1' ? 'checked' : '' ?>>
                    <label for="reg" style="margin:0">Allow Public Registration</label>
                </div>
                <div class="form-group" style="display:flex;align-items:center;gap:8px">
                    <input type="checkbox" name="maintenance_mode" id="maint" value="1" <?= ($settings['maintenance_mode'] ?? '0')=='1' ? 'checked' : '' ?>>
                    <label for="maint" style="margin:0">Maintenance Mode</label>
                </div>
            </div>
        </section>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">💾 Save Settings</button>
        </div>
    </form>
</div>
