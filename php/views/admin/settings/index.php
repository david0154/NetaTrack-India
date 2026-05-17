<?php $page_title = 'Settings'; ?>
<div class="settings-page">
    <h2>⚙️ Site Settings</h2>

    <form method="POST" action="<?= url('admin/settings') ?>" class="admin-form" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <!-- General -->
        <section class="form-section">
            <h3>🌐 General</h3>
            <div class="form-grid-2">
                <div class="form-group"><label>Site Name</label><input type="text" name="site_name" value="<?= e($settings['site_name'] ?? '') ?>"></div>
                <div class="form-group"><label>Tagline</label><input type="text" name="site_tagline" value="<?= e($settings['site_tagline'] ?? '') ?>"></div>
                <div class="form-group"><label>Site URL</label><input type="url" name="site_url" value="<?= e($settings['site_url'] ?? '') ?>"></div>
                <div class="form-group"><label>Logo URL</label><input type="url" name="site_logo" value="<?= e($settings['site_logo'] ?? '') ?>"></div>
            </div>
            <div class="form-group"><label>Meta Description</label><textarea name="meta_description" rows="2"><?= e($settings['meta_description'] ?? '') ?></textarea></div>
        </section>

        <!-- AI APIs -->
        <section class="form-section">
            <h3>🤖 AI APIs <small style="color:#64748b;font-weight:400;font-size:.85em">Add any — system auto-selects first available (Gemini → OpenAI → OpenRouter → Claude → Sarvam)</small></h3>
            <div class="alert-info" style="margin-bottom:1rem;padding:.65rem 1rem;background:rgba(59,130,246,.1);border-radius:8px;font-size:.85rem;color:#93c5fd">
                💡 Add as many API keys as you want. The AI router automatically falls back to the next available provider if one fails.
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Gemini API Key</label>
                    <input type="password" name="gemini_api_key" value="<?= e($settings['gemini_api_key'] ?? '') ?>" placeholder="AIza..." autocomplete="new-password">
                    <small>Google AI Studio — free tier available</small>
                </div>
                <div class="form-group">
                    <label>OpenAI API Key</label>
                    <input type="password" name="openai_api_key" value="<?= e($settings['openai_api_key'] ?? '') ?>" placeholder="sk-..." autocomplete="new-password">
                    <small>GPT-4o Mini / GPT-4o</small>
                </div>
                <div class="form-group">
                    <label>OpenRouter API Key</label>
                    <input type="password" name="openrouter_api_key" value="<?= e($settings['openrouter_api_key'] ?? '') ?>" placeholder="sk-or-..." autocomplete="new-password">
                    <small>Access 100+ models incl. free Llama, Mistral</small>
                </div>
                <div class="form-group">
                    <label>Claude API Key (Anthropic)</label>
                    <input type="password" name="claude_api_key" value="<?= e($settings['claude_api_key'] ?? '') ?>" placeholder="sk-ant-..." autocomplete="new-password">
                    <small>Claude 3 Haiku / Sonnet</small>
                </div>
                <div class="form-group">
                    <label>Sarvam AI Key</label>
                    <input type="password" name="sarvam_api_key" value="<?= e($settings['sarvam_api_key'] ?? '') ?>" placeholder="sarvam-..." autocomplete="new-password">
                    <small>India-focused AI, supports Hindi/regional languages</small>
                </div>
                <div class="form-group" style="display:flex;align-items:center;gap:8px;padding-top:1.5rem">
                    <input type="checkbox" name="ai_enabled" id="ai_enabled" value="1" <?= ($settings['ai_enabled'] ?? '1')=='1' ? 'checked' : '' ?>>
                    <label for="ai_enabled" style="margin:0">Enable AI Features</label>
                </div>
            </div>
        </section>

        <!-- Push API -->
        <section class="form-section">
            <h3>🔗 Python Admin → Push API</h3>
            <p style="color:#64748b;font-size:.875rem;margin-bottom:1rem">The Python desktop admin app uses this token to push leader data, scores, cases and announcements directly to the site.</p>
            <div class="form-grid-2">
                <div class="form-group">
                    <label>Admin API Token</label>
                    <div style="display:flex;gap:8px">
                        <input type="text" name="admin_api_token" id="admin_api_token"
                               value="<?= e($settings['admin_api_token'] ?? '') ?>"
                               placeholder="Auto-generated on install" style="flex:1">
                        <button type="button" onclick="generateToken()" class="btn btn-secondary" style="white-space:nowrap">🔄 Generate</button>
                    </div>
                    <small>Keep this secret. Used for Python admin → site push.</small>
                </div>
            </div>
        </section>

        <!-- Analytics & Ads -->
        <section class="form-section">
            <h3>📊 Analytics & Ads</h3>
            <div class="form-grid-2">
                <div class="form-group"><label>Google Analytics ID</label><input type="text" name="google_analytics_id" value="<?= e($settings['google_analytics_id'] ?? '') ?>" placeholder="G-XXXXXXXXXX"></div>
                <div class="form-group"><label>Google AdSense Code / Publisher ID</label><input type="text" name="google_adsense_code" value="<?= e($settings['google_adsense_code'] ?? '') ?>" placeholder="ca-pub-xxxxxxxxxxxxxxxx"></div>
            </div>
        </section>

        <!-- Sponsor & Announcement -->
        <section class="form-section">
            <h3>📣 Sponsor & Announcement</h3>
            <div class="form-grid-2">
                <div class="form-group"><label>Sponsor Title</label><input type="text" name="sponsor_title" value="<?= e($settings['sponsor_title'] ?? '') ?>"></div>
                <div class="form-group"><label>Announcement Link</label><input type="url" name="announcement_link" value="<?= e($settings['announcement_link'] ?? '') ?>"></div>
                <div class="form-group" style="grid-column:1/-1"><label>Sponsor HTML</label><textarea name="sponsor_html" rows="2"><?= e($settings['sponsor_html'] ?? '') ?></textarea></div>
                <div class="form-group" style="grid-column:1/-1"><label>Announcement Text</label><textarea name="announcement_text" rows="2"><?= e($settings['announcement_text'] ?? '') ?></textarea></div>
                <div class="form-group" style="display:flex;align-items:center;gap:8px">
                    <input type="checkbox" name="announcement_active" id="announcement_active" value="1" <?= ($settings['announcement_active'] ?? '0')=='1' ? 'checked' : '' ?>>
                    <label for="announcement_active" style="margin:0">Enable Announcement Bar</label>
                </div>
            </div>
        </section>

        <!-- SMTP -->
        <section class="form-section">
            <h3>📧 SMTP Email</h3>
            <div class="form-grid-2">
                <div class="form-group"><label>SMTP Host</label><input type="text" name="smtp_host" value="<?= e($settings['smtp_host'] ?? '') ?>"></div>
                <div class="form-group"><label>SMTP Port</label><input type="number" name="smtp_port" value="<?= e($settings['smtp_port'] ?? 587) ?>"></div>
                <div class="form-group"><label>SMTP User</label><input type="text" name="smtp_user" value="<?= e($settings['smtp_user'] ?? '') ?>"></div>
                <div class="form-group"><label>SMTP Password</label><input type="password" name="smtp_pass" value="<?= e($settings['smtp_pass'] ?? '') ?>"></div>
            </div>
        </section>

        <!-- Access Control -->
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

<script>
function generateToken() {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let token = '';
    for (let i = 0; i < 64; i++) token += chars.charAt(Math.floor(Math.random() * chars.length));
    document.getElementById('admin_api_token').value = token;
}
</script>
