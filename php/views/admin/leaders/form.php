<?php
// Variables: $leader (null=create, array=edit), $states, $parties
$editing    = !empty($leader);
$page_title = ($editing ? 'Edit Leader' : 'Add Leader') . ' — NetaTrack Admin';
$action     = $editing ? url('admin/leaders/'.$leader['id']) : url('admin/leaders');
?>
<div class="page-header">
  <div>
    <div class="page-title"><?= $editing ? 'Edit Leader' : 'Add New Leader' ?>
      <span><?= $editing ? 'Update leader information and scores' : 'Add a new political leader to the platform' ?></span>
    </div>
  </div>
  <a href="<?= url('admin/leaders') ?>" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<form method="POST" action="<?= $action ?>" enctype="multipart/form-data">
  <?= csrf_field() ?>

  <div class="grid-2" style="align-items:start">

    <!-- Left Column -->
    <div style="display:flex;flex-direction:column;gap:1.25rem">

      <div class="card">
        <div class="card-header"><div class="card-title">Basic Information</div></div>
        <div class="form-group">
          <label class="form-label">Full Name *</label>
          <input type="text" name="name" class="form-control" value="<?= e($leader['name']??old('name')) ?>" required placeholder="e.g. Narendra Modi">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">State</label>
            <select name="state_id" class="form-control">
              <option value="">Select State</option>
              <?php foreach($states as $s): ?>
                <option value="<?= $s['id'] ?>" <?= ($leader['state_id']??old('state_id'))==$s['id']?'selected':'' ?>><?= e($s['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Party</label>
            <select name="party_id" class="form-control">
              <option value="">Select Party</option>
              <?php foreach($parties as $p): ?>
                <option value="<?= $p['id'] ?>" <?= ($leader['party_id']??old('party_id'))==$p['id']?'selected':'' ?>><?= e($p['abbreviation']??$p['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Constituency</label>
            <input type="text" name="constituency" class="form-control" value="<?= e($leader['constituency']??old('constituency')) ?>" placeholder="e.g. Varanasi">
          </div>
          <div class="form-group">
            <label class="form-label">Designation</label>
            <input type="text" name="designation" class="form-control" value="<?= e($leader['designation']??old('designation')) ?>" placeholder="e.g. Prime Minister">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Term Start</label>
            <input type="date" name="term_start" class="form-control" value="<?= e($leader['term_start']??'') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Term End</label>
            <input type="date" name="term_end" class="form-control" value="<?= e($leader['term_end']??'') ?>">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Date of Birth</label>
            <input type="date" name="dob" class="form-control" value="<?= e($leader['dob']??'') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Gender</label>
            <select name="gender" class="form-control">
              <option value="Male" <?= ($leader['gender']??'Male')==='Male'?'selected':'' ?>>Male</option>
              <option value="Female" <?= ($leader['gender']??'')==='Female'?'selected':'' ?>>Female</option>
              <option value="Other" <?= ($leader['gender']??'')==='Other'?'selected':'' ?>>Other</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Bio / Description</label>
          <textarea name="bio" class="form-control" rows="3" placeholder="Brief biography..."><?= e($leader['bio']??old('bio')) ?></textarea>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Criminal Cases</label>
            <input type="number" name="criminal_cases" class="form-control" min="0" value="<?= e($leader['criminal_cases']??0) ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Assets Declared (₹)</label>
            <input type="number" name="assets_declared" class="form-control" step="0.01" min="0" value="<?= e($leader['assets_declared']??0) ?>">
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><div class="card-title">Social & Contact</div></div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= e($leader['email']??'') ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Website</label>
            <input type="url" name="website" class="form-control" value="<?= e($leader['website']??'') ?>" placeholder="https://">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Twitter / X</label>
            <input type="text" name="twitter" class="form-control" value="<?= e($leader['twitter']??'') ?>" placeholder="@handle">
          </div>
          <div class="form-group">
            <label class="form-label">Facebook</label>
            <input type="text" name="facebook" class="form-control" value="<?= e($leader['facebook']??'') ?>">
          </div>
        </div>
      </div>
    </div>

    <!-- Right Column -->
    <div style="display:flex;flex-direction:column;gap:1.25rem">

      <div class="card">
        <div class="card-header"><div class="card-title">Leader Score System</div></div>
        <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:1rem">Adjust AI score values. Total score is calculated automatically using weighted formula.</p>

        <?php
        $scoreFields = [
          'score_promise_completion'   => ['Promise Completion','30%','success'],
          'score_project_delivery'     => ['Project Delivery','20%','info'],
          'score_budget_efficiency'    => ['Budget Efficiency','15%','purple'],
          'score_public_satisfaction'  => ['Public Satisfaction','10%','warning'],
          'score_transparency'         => ['Transparency Score','10%','info'],
          'score_verification_trust'   => ['Verification Trust','15%','success'],
          'score_corruption'           => ['Corruption Allegations','-20%','danger'],
          'score_fake_claims'          => ['Fake Claims','-10%','danger'],
        ];
        foreach($scoreFields as $field => [$label, $weight, $color]): ?>
        <div class="score-bar-wrap">
          <div class="score-bar-label">
            <span><?= $label ?> <span style="color:var(--text-muted)">(?= $weight ?)</span></span>
            <span id="val_<?= $field ?>"><?= $leader[$field]??50 ?></span>
          </div>
          <input type="range" name="<?= $field ?>" min="0" max="100" value="<?= $leader[$field]??50 ?>"
            oninput="document.getElementById('val_<?= $field ?>').textContent=this.value"
            style="width:100%;accent-color:var(--color-primary);cursor:pointer;margin-bottom:.4rem">
        </div>
        <?php endforeach; ?>

        <div style="background:rgba(59,130,246,0.1);border:1px solid rgba(59,130,246,0.2);border-radius:8px;padding:.75rem;margin-top:.5rem">
          <div style="font-size:.8rem;color:var(--text-muted)">Calculated Total Score (auto)</div>
          <div style="font-size:1.75rem;font-weight:800;color:var(--color-primary)" id="previewScore"><?= $leader['total_score']??50 ?></div>
        </div>
      </div>

      <div class="card">
        <div class="card-header"><div class="card-title">Photo & Status</div></div>
        <?php if(!empty($leader['photo'])): ?>
          <img src="<?= e($leader['photo']) ?>" style="width:80px;height:80px;border-radius:50%;object-fit:cover;margin-bottom:1rem">
        <?php endif; ?>
        <div class="form-group">
          <label class="form-label">Photo URL</label>
          <input type="url" name="photo" class="form-control" value="<?= e($leader['photo']??'') ?>" placeholder="https://...">
        </div>
        <div class="form-group">
          <label class="form-label">Status</label>
          <select name="status" class="form-control">
            <option value="active" <?= ($leader['status']??'active')==='active'?'selected':'' ?>>Active</option>
            <option value="inactive" <?= ($leader['status']??'')==='inactive'?'selected':'' ?>>Inactive</option>
            <option value="archived" <?= ($leader['status']??'')==='archived'?'selected':'' ?>>Archived</option>
          </select>
        </div>
        <div class="form-group" style="display:flex;align-items:center;gap:.75rem">
          <label class="toggle-switch">
            <input type="checkbox" name="is_verified" value="1" <?= ($leader['is_verified']??0)?'checked':'' ?>>
            <span class="toggle-slider"></span>
          </label>
          <span class="form-label" style="margin:0">Mark as Verified</span>
        </div>
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%;padding:.85rem">
        <i class="fas fa-save"></i> <?= $editing ? 'Update Leader' : 'Add Leader' ?>
      </button>
    </div>
  </div>
</form>

<script>
// Live score preview
function recalcScore() {
  const w = {promise_completion:.30,project_delivery:.20,budget_efficiency:.15,
              public_satisfaction:.10,transparency:.10,verification_trust:.15};
  const neg = {corruption:.20, fake_claims:.10};
  let score = 0;
  for(const [k,wt] of Object.entries(w)) {
    const el = document.querySelector(`[name="score_${k}"]`);
    if(el) score += parseFloat(el.value) * wt;
  }
  for(const [k,wt] of Object.entries(neg)) {
    const el = document.querySelector(`[name="score_${k}"]`);
    if(el) score -= parseFloat(el.value) * wt;
  }
  score = Math.max(0, Math.min(100, Math.round(score)));
  document.getElementById('previewScore').textContent = score;
}
document.querySelectorAll('input[type="range"]').forEach(r => r.addEventListener('input', recalcScore));
</script>
