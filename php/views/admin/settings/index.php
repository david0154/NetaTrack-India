<?php $page_title = 'Settings — NetaTrack Admin'; ?>
<div class="page-header">
  <div class="page-title">Settings
    <span>Configure platform behavior, AI keys, and appearance</span>
  </div>
</div>

<form method="POST" action="<?= url('admin/settings') ?>">
  <?= csrf_field() ?>
  <div class="grid-2" style="align-items:start;gap:1.25rem">

    <!-- General -->
    <div style="display:flex;flex-direction:column;gap:1.25rem">
      <div class="card">
        <div class="card-header"><div class="card-title"><i class="fas fa-globe"></i> General</div></div>
        <?php
          $fields = [
            'site_name'        => ['Site Name',    'text',  'NetaTrack India'],
            'site_tagline'     => ['Tagline',       'text',  'Tracking Political Accountability'],
            'site_email'       => ['Contact Email', 'email', 'contact@netatrack.in'],
            'site_url'         => ['Site URL',      'url',   'https://netatrack.in'],
            'meta_description' => ['Meta Description','textarea',''],
          ];
          foreach($fields as $key => [$label,$type,$placeholder]):
        ?>
        <div class="form-group">
          <label class="form-label"><?= $label ?></label>
          <?php if($type==='textarea'): ?>
            <textarea name="<?= $key ?>" class="form-control" rows="2"><?= e($settings[$key]??$placeholder) ?></textarea>
          <?php else: ?>
            <input type="<?= $type ?>" name="<?= $key ?>" class="form-control"
              value="<?= e($settings[$key]??$placeholder) ?>">
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="card">
        <div class="card-header"><div class="card-title"><i class="fas fa-envelope"></i> SMTP Email</div></div>
        <?php
          $smtp = [
            'smtp_host'     => ['SMTP Host',     'text',     'smtp.gmail.com'],
            'smtp_port'     => ['SMTP Port',     'number',   '587'],
            'smtp_username' => ['SMTP Username', 'email',    ''],
            'smtp_password' => ['SMTP Password', 'password', ''],
            'smtp_from_name'=> ['From Name',     'text',     'NetaTrack India'],
          ];
          foreach($smtp as $key => [$label,$type,$placeholder]):
        ?>
        <div class="form-group">
          <label class="form-label"><?= $label ?></label>
          <input type="<?= $type ?>" name="<?= $key ?>" class="form-control"
            value="<?= $type==='password'?'':e($settings[$key]??'') ?>"
            placeholder="<?= $type==='password'?'Leave blank to keep current':$placeholder ?>">
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- AI & Features -->
    <div style="display:flex;flex-direction:column;gap:1.25rem">
      <div class="card">
        <div class="card-header"><div class="card-title"><i class="fas fa-robot"></i> AI Configuration</div></div>
        <div class="form-group">
          <label class="form-label">Gemini API Key</label>
          <input type="password" name="gemini_api_key" class="form-control" placeholder="Leave blank to keep current">
          <div class="form-hint">Used for fake claim detection and news analysis</div>
        </div>
        <div class="form-group">
          <label class="form-label">NewsAPI Key</label>
          <input type="password" name="newsapi_key" class="form-control" placeholder="Leave blank to keep current">
        </div>
        <div class="form-group">
          <label class="form-label">Fact Check API Key</label>
          <input type="password" name="fact_check_api_key" class="form-control" placeholder="Leave blank to keep current">
        </div>
        <div class="form-group">
          <label class="form-label">Scraper Interval (hours)</label>
          <input type="number" name="scraper_interval_hours" class="form-control" min="1" max="168"
            value="<?= e($settings['scraper_interval_hours']??6) ?>">
        </div>
        <?php
          $toggles = [
            'ai_enabled'             => 'Enable AI Analysis',
            'scraper_enabled'        => 'Enable Auto Scraper',
            'ai_fake_detection'      => 'AI Fake Claim Detection',
            'public_reports_enabled' => 'Allow Public Reports',
          ];
          foreach($toggles as $key => $label):
        ?>
        <div class="form-group" style="display:flex;align-items:center;justify-content:space-between">
          <span class="form-label" style="margin:0"><?= $label ?></span>
          <label class="toggle-switch">
            <input type="checkbox" name="<?= $key ?>" value="1" <?= ($settings[$key]??'1')==='1'?'checked':'' ?>>
            <span class="toggle-slider"></span>
          </label>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="card">
        <div class="card-header"><div class="card-title"><i class="fas fa-shield-alt"></i> Security</div></div>
        <?php
          $secToggles = [
            'maintenance_mode'   => 'Maintenance Mode',
            'user_registration'  => 'Allow User Registration',
            'rate_limiting'      => 'Enable Rate Limiting',
          ];
          foreach($secToggles as $key => $label):
        ?>
        <div class="form-group" style="display:flex;align-items:center;justify-content:space-between">
          <span class="form-label" style="margin:0"><?= $label ?></span>
          <label class="toggle-switch">
            <input type="checkbox" name="<?= $key ?>" value="1" <?= ($settings[$key]??'1')==='1'?'checked':'' ?>>
            <span class="toggle-slider"></span>
          </label>
        </div>
        <?php endforeach; ?>
        <div class="form-group">
          <label class="form-label">Login Attempts Before Lockout</label>
          <input type="number" name="login_max_attempts" class="form-control" min="3" max="20"
            value="<?= e($settings['login_max_attempts']??5) ?>">
        </div>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;padding:.85rem">
        <i class="fas fa-save"></i> Save All Settings
      </button>
    </div>
  </div>
</form>
