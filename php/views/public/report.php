<?php
// Variables: $leaders, $states
$page_title     = 'Submit a Report';
$page_meta_desc = 'Report corruption, broken promises or delayed projects. Anonymous submissions accepted.';
?>
<div class="section">
  <div class="section-inner">
    <div class="section-header">
      <div class="section-badge" style="background:rgba(249,115,22,.1);border-color:rgba(249,115,22,.25);color:var(--color-saffron)">
        <i class="fas fa-flag"></i> Citizen Reports
      </div>
      <h1 class="section-title">Report <span class="hl">Corruption</span></h1>
      <p class="section-desc">Your voice matters. Submit a report and help hold politicians accountable. All reports are AI-verified before publication.</p>
    </div>

    <div class="report-form-wrap">

      <?php if($msg = flash('success')): ?>
        <div class="alert-pub success" style="margin-bottom:1.5rem">
          <i class="fas fa-check-circle"></i> <?= e($msg) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="<?= url('report') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <!-- Step 1: Report Type -->
        <div class="glass-card" style="padding:1.5rem;margin-bottom:1.25rem">
          <h3 style="font-size:.9rem;font-weight:700;margin-bottom:1rem;color:var(--text-secondary)">
            <span style="background:linear-gradient(135deg,var(--color-saffron),var(--color-primary));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">Step 1</span>
            &mdash; What are you reporting?
          </h3>
          <div class="report-type-grid" id="reportTypeGrid">
            <?php
              $types=[
                ['corruption',    'fas fa-hand-holding-usd','#ef4444','Corruption / Bribery'],
                ['fake_claim',    'fas fa-robot',           '#f59e0b','Fake / Misleading Claim'],
                ['project_delay', 'fas fa-hard-hat',        '#f97316','Project Delay'],
                ['promise_broken','fas fa-handshake-slash', '#8b5cf6','Promise Broken'],
                ['positive',      'fas fa-thumbs-up',       '#22c55e','Positive Work Done'],
                ['other',         'fas fa-ellipsis-h',      '#64748b','Other'],
              ];
              foreach($types as [$val,$icon,$color,$label]):
            ?>
            <label style="cursor:pointer">
              <input type="radio" name="type" value="<?= $val ?>" style="display:none"
                <?= ($val==='corruption')?'checked':'' ?>>
              <div class="report-type-btn" id="rt-<?= $val ?>" onclick="selectType('<?= $val ?>')">
                <div class="rt-icon" style="color:<?= $color ?>"><i class="<?= $icon ?>"></i></div>
                <div class="rt-label"><?= $label ?></div>
              </div>
            </label>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Step 2: Details -->
        <div class="glass-card" style="padding:1.5rem;margin-bottom:1.25rem">
          <h3 style="font-size:.9rem;font-weight:700;margin-bottom:1rem;color:var(--text-secondary)">
            <span style="background:linear-gradient(135deg,var(--color-saffron),var(--color-primary));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">Step 2</span>
            &mdash; Tell us what happened
          </h3>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem">
            <div>
              <label style="display:block;font-size:.8rem;font-weight:600;color:var(--text-secondary);margin-bottom:.35rem">Related Leader (optional)</label>
              <select name="leader_id" class="form-control-pub">
                <option value="">Select Leader</option>
                <?php foreach($leaders??[] as $l): ?>
                  <option value="<?= $l['id'] ?>" <?= ($_GET['leader_id']??'')==$l['id']?'selected':'' ?>>
                    <?= e($l['name']) ?> (<?= e($l['party_abbr']??'') ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label style="display:block;font-size:.8rem;font-weight:600;color:var(--text-secondary);margin-bottom:.35rem">State</label>
              <select name="state_id" class="form-control-pub">
                <option value="">Select State</option>
                <?php foreach($states??[] as $st): ?>
                  <option value="<?= $st['id'] ?>"><?= e($st['name']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div style="margin-bottom:1rem">
            <label style="display:block;font-size:.8rem;font-weight:600;color:var(--text-secondary);margin-bottom:.35rem">Report Title *</label>
            <input type="text" name="title" class="form-control-pub" required
              placeholder="Brief title: e.g. &#039;Rs 500 Cr scam in road construction project&#039;"
              value="<?= e(old('title')) ?>">
          </div>

          <div style="margin-bottom:1rem">
            <label style="display:block;font-size:.8rem;font-weight:600;color:var(--text-secondary);margin-bottom:.35rem">Detailed Description *</label>
            <textarea name="description" class="form-control-pub" rows="5" required
              placeholder="Provide as much detail as possible. Include dates, amounts, locations and names if known."><?= e(old('description')) ?></textarea>
          </div>

          <div style="margin-bottom:1rem">
            <label style="display:block;font-size:.8rem;font-weight:600;color:var(--text-secondary);margin-bottom:.35rem">Evidence / Sources (URLs)</label>
            <textarea name="evidence_urls" class="form-control-pub" rows="2"
              placeholder="Paste news article links, social media posts, or official documents (one per line)"><?= e(old('evidence_urls')) ?></textarea>
          </div>
        </div>

        <!-- Step 3: Identity -->
        <div class="glass-card" style="padding:1.5rem;margin-bottom:1.25rem">
          <h3 style="font-size:.9rem;font-weight:700;margin-bottom:.5rem;color:var(--text-secondary)">
            <span style="background:linear-gradient(135deg,var(--color-saffron),var(--color-primary));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">Step 3</span>
            &mdash; Your Identity (Optional)
          </h3>
          <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:1rem">
            <i class="fas fa-lock"></i> Anonymous reports are accepted. Your identity will never be publicly disclosed.
          </p>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
            <div>
              <label style="display:block;font-size:.8rem;font-weight:600;color:var(--text-secondary);margin-bottom:.35rem">Your Name</label>
              <input type="text" name="reporter_name" class="form-control-pub" placeholder="Anonymous"
                value="<?= auth()->check() ? e(auth()->user()['name']??'') : e(old('reporter_name')) ?>">
            </div>
            <div>
              <label style="display:block;font-size:.8rem;font-weight:600;color:var(--text-secondary);margin-bottom:.35rem">Email (for follow-up)</label>
              <input type="email" name="reporter_email" class="form-control-pub" placeholder="optional@email.com"
                value="<?= auth()->check() ? e(auth()->user()['email']??'') : e(old('reporter_email')) ?>">
            </div>
          </div>
        </div>

        <!-- Privacy notice -->
        <div style="background:rgba(59,130,246,.06);border:1px solid rgba(59,130,246,.15);border-radius:var(--radius-sm);padding:1rem;margin-bottom:1.25rem;font-size:.8rem;color:var(--text-muted);line-height:1.65">
          <i class="fas fa-info-circle" style="color:#3b82f6"></i>
          All submissions are reviewed by our AI system and admin team before publication.
          False reports may be flagged. By submitting, you agree to our <a href="#" style="color:#3b82f6">Terms of Use</a>.
        </div>

        <button type="submit" class="btn-hero-primary" style="width:100%;padding:1rem;font-size:1rem;justify-content:center">
          <i class="fas fa-paper-plane"></i> Submit Report for Review
        </button>
      </form>
    </div>
  </div>
</div>

<script>
function selectType(val) {
  document.querySelectorAll('.report-type-btn').forEach(btn=>{
    btn.classList.remove('selected');
  });
  const selected = document.getElementById('rt-'+val);
  if(selected) selected.classList.add('selected');
  const radio = document.querySelector(`input[name="type"][value="${val}"]`);
  if(radio) radio.checked = true;
}
// Highlight default
selectType('corruption');
</script>
