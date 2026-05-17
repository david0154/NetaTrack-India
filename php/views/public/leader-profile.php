<?php
// Variables: $leader, $promises (paginated), $projects, $reports, $party
$page_title     = e($leader['name']);
$page_meta_desc = 'Track '.e($leader['name']).' - promises, projects and accountability score.';
$rank      = $leader['score_rank']??'Average';
$ringClass = strtolower($rank);
$badgeMap  = ['excellent'=>'success','good'=>'info','average'=>'warning','poor'=>'danger'];
?>

<!-- Profile Header -->
<div style="padding:2.5rem 1.5rem 0;position:relative;overflow:hidden">
  <div style="max-width:1100px;margin:0 auto">
    <!-- Party color top bar -->
    <div style="height:4px;background:<?= e($leader['party_color']??'linear-gradient(90deg,#3b82f6,#8b5cf6)') ?>;border-radius:4px;margin-bottom:2rem"></div>

    <div style="display:flex;gap:2rem;align-items:flex-start;flex-wrap:wrap">
      <!-- Avatar -->
      <div style="flex-shrink:0">
        <div style="width:120px;height:120px;border-radius:50%;overflow:hidden;border:3px solid <?= e($leader['party_color']??'var(--color-primary)') ?>;box-shadow:0 0 32px <?= e($leader['party_color']??'rgba(59,130,246,.4)') ?>">
          <?php if($leader['photo']): ?>
            <img src="<?= e($leader['photo']) ?>" style="width:100%;height:100%;object-fit:cover">
          <?php else: ?>
            <div style="width:100%;height:100%;background:linear-gradient(135deg,var(--color-primary),var(--color-secondary));display:flex;align-items:center;justify-content:center;font-size:2.5rem;font-weight:900;color:#fff">
              <?= strtoupper(substr($leader['name'],0,1)) ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Info -->
      <div style="flex:1;min-width:0">
        <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;margin-bottom:.5rem">
          <h1 style="font-size:clamp(1.4rem,3vw,2rem);font-weight:900"><?= e($leader['name']) ?></h1>
          <?php if($leader['is_verified']??0): ?>
            <span class="badge badge-info"><i class="fas fa-check-circle"></i> Verified</span>
          <?php endif; ?>
          <span class="badge badge-<?= $badgeMap[$ringClass]??'muted' ?>"><?= $rank ?></span>
        </div>
        <div style="font-size:1rem;color:var(--text-secondary);margin-bottom:.5rem"><?= e($leader['designation']??'Political Leader') ?></div>
        <div style="display:flex;gap:1.5rem;flex-wrap:wrap;font-size:.85rem;color:var(--text-muted);margin-bottom:1rem">
          <?php if($leader['party_name']): ?>
            <span><span style="color:<?= e($leader['party_color']??'#3b82f6') ?>">●</span> <?= e($leader['party_name']) ?></span>
          <?php endif; ?>
          <?php if($leader['state_name']): ?>
            <span><i class="fas fa-map-marker-alt"></i> <?= e($leader['state_name']) ?></span>
          <?php endif; ?>
          <?php if($leader['constituency']): ?>
            <span><i class="fas fa-building"></i> <?= e($leader['constituency']) ?></span>
          <?php endif; ?>
          <?php if($leader['term_start']): ?>
            <span><i class="fas fa-calendar"></i> Since <?= date('M Y',strtotime($leader['term_start'])) ?></span>
          <?php endif; ?>
        </div>
        <?php if($leader['bio']): ?>
          <p style="font-size:.88rem;color:var(--text-secondary);max-width:700px;line-height:1.7"><?= e($leader['bio']) ?></p>
        <?php endif; ?>
        <!-- Social -->
        <div style="display:flex;gap:.6rem;margin-top:1rem">
          <?php if($leader['twitter']): ?>
            <a href="https://twitter.com/<?= ltrim($leader['twitter'],'@') ?>" target="_blank" class="social-btn" style="width:32px;height:32px"><i class="fab fa-twitter"></i></a>
          <?php endif; ?>
          <?php if($leader['facebook']): ?>
            <a href="<?= e($leader['facebook']) ?>" target="_blank" class="social-btn" style="width:32px;height:32px"><i class="fab fa-facebook"></i></a>
          <?php endif; ?>
          <?php if($leader['website']): ?>
            <a href="<?= e($leader['website']) ?>" target="_blank" class="social-btn" style="width:32px;height:32px"><i class="fas fa-globe"></i></a>
          <?php endif; ?>
          <a href="<?= url('report?leader_id='.$leader['id']) ?>" class="btn-nav btn-nav-outline" style="font-size:.78rem">
            <i class="fas fa-flag"></i> Submit Report
          </a>
        </div>
      </div>

      <!-- Score Card -->
      <div class="glass-card" style="padding:1.5rem;min-width:220px;text-align:center">
        <div style="font-size:.75rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.75rem">Accountability Score</div>
        <div style="font-size:3.5rem;font-weight:900;line-height:1;color:var(--color-<?= $ringClass==='excellent'?'success':($ringClass==='good'?'accent':($ringClass==='average'?'warning':'danger')) ?>)">
          <?= $leader['total_score'] ?>
        </div>
        <div style="font-size:.75rem;color:var(--text-muted);margin:.25rem 0 1rem">out of 100</div>
        <div style="display:flex;flex-direction:column;gap:.5rem">
          <?php
            $scoreFields=[
              ['Promise Completion',$leader['score_promise_completion']??50,'success'],
              ['Project Delivery',$leader['score_project_delivery']??50,'info'],
              ['Transparency',$leader['score_transparency']??50,'purple'],
              ['Public Satisfaction',$leader['score_public_satisfaction']??50,'warning'],
            ];
            foreach($scoreFields as [$lbl,$val,$cls]):
          ?>
          <div style="font-size:.72rem">
            <div style="display:flex;justify-content:space-between;color:var(--text-muted);margin-bottom:.2rem">
              <span><?= $lbl ?></span><span style="font-weight:700;color:var(--text-primary)"><?= $val ?></span>
            </div>
            <div class="score-bar-h">
              <div class="score-bar-fill <?= $cls ?>" style="width:<?= $val ?>%"></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- Tabs -->
    <div style="display:flex;gap:.25rem;border-bottom:1px solid var(--border-glass);margin-top:2rem" id="profileTabs">
      <?php foreach(['promises'=>'Promises','projects'=>'Projects','reports'=>'Reports','background'=>'Background'] as $tab=>$label): ?>
        <button onclick="showTab('<?= $tab ?>')" id="tab-<?= $tab ?>"
          style="padding:.65rem 1.25rem;background:none;border:none;cursor:pointer;font-size:.875rem;font-weight:600;
                 color:var(--text-muted);border-bottom:2px solid transparent;transition:.2s;"
          class="profile-tab"><?= $label ?></button>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- Tab Content -->
<div style="max-width:1100px;margin:0 auto;padding:1.5rem">

  <!-- Promises Tab -->
  <div id="tab-promises-content">
    <div class="promises-grid">
      <?php foreach($promises['data']??[] as $promise): ?>
      <?php
        $sc=['kept'=>'success','broken'=>'danger','in_progress'=>'info','partial'=>'warning','expired'=>'muted'];
        $bc=['kept'=>'#22c55e','broken'=>'#ef4444','in_progress'=>'#06b6d4','partial'=>'#f59e0b','expired'=>'#64748b'];
      ?>
      <div class="glass-card promise-card" style="--promise-color:<?= $bc[$promise['status']]??'#64748b' ?>">
        <div class="promise-title"><?= e($promise['title']) ?></div>
        <div class="promise-meta">
          <?php if($promise['deadline']): ?>
            <span><i class="fas fa-clock"></i> <?= date('d M Y',strtotime($promise['deadline'])) ?></span>
          <?php endif; ?>
          <?php if($promise['category']): ?>
            <span><i class="fas fa-tag"></i> <?= e($promise['category']) ?></span>
          <?php endif; ?>
        </div>
        <?php if($promise['description']): ?>
          <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:.75rem"><?= e(truncate($promise['description'],120)) ?></p>
        <?php endif; ?>
        <div style="display:flex;align-items:center;justify-content:space-between">
          <span class="badge badge-<?= $sc[$promise['status']]??'muted' ?>"><?= e(str_replace('_',' ',$promise['status'])) ?></span>
          <?php if($promise['ai_confidence']??0): ?>
            <span style="font-size:.72rem;color:var(--text-muted)"><i class="fas fa-robot"></i> <?= $promise['ai_confidence'] ?>%</span>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if(empty($promises['data'])): ?>
        <p style="color:var(--text-muted);text-align:center;padding:2rem;grid-column:1/-1">No promises tracked yet.</p>
      <?php endif; ?>
    </div>
  </div>

  <!-- Projects Tab -->
  <div id="tab-projects-content" style="display:none">
    <div class="grid-auto">
      <?php foreach($projects??[] as $proj): ?>
      <?php $sc=['completed'=>'success','in_progress'=>'info','planned'=>'muted','delayed'=>'danger','cancelled'=>'warning']; ?>
      <div class="glass-card" style="padding:1.25rem">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.5rem">
          <span class="badge badge-<?= $sc[$proj['status']]??'muted' ?>"><?= e(str_replace('_',' ',$proj['status'])) ?></span>
          <?php if($proj['budget_crore']??0): ?>
            <span style="font-size:.8rem;color:var(--text-muted)">₹<?= number_format($proj['budget_crore'],0) ?>Cr</span>
          <?php endif; ?>
        </div>
        <h4 style="font-size:.9rem;font-weight:700;color:var(--text-primary);margin-bottom:.35rem"><?= e($proj['title']) ?></h4>
        <p style="font-size:.8rem;color:var(--text-muted);margin-bottom:.75rem"><?= e(truncate($proj['description']??'',100)) ?></p>
        <?php if($proj['completion_pct']??0): ?>
          <div style="font-size:.72rem;color:var(--text-muted);margin-bottom:.25rem">Progress: <?= $proj['completion_pct'] ?>%</div>
          <div class="score-bar-h">
            <div class="score-bar-fill" style="width:<?= $proj['completion_pct'] ?>%"></div>
          </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Reports Tab -->
  <div id="tab-reports-content" style="display:none">
    <?php foreach($reports??[] as $report): ?>
    <?php $tc=['corruption'=>'danger','fake_claim'=>'warning','project_delay'=>'saffron','promise_broken'=>'danger','positive'=>'success','other'=>'muted']; ?>
    <div class="glass-card" style="padding:1.25rem;margin-bottom:.75rem;display:flex;gap:1rem;align-items:flex-start">
      <span class="badge badge-<?= $tc[$report['type']]??'muted' ?>" style="flex-shrink:0;margin-top:.15rem">
        <?= e(str_replace('_',' ',$report['type'])) ?>
      </span>
      <div>
        <div style="font-weight:600;font-size:.9rem;color:var(--text-primary);margin-bottom:.25rem"><?= e($report['title']) ?></div>
        <div style="font-size:.8rem;color:var(--text-muted)"><?= e(truncate($report['description']??'',150)) ?></div>
        <div style="font-size:.72rem;color:var(--text-muted);margin-top:.4rem"><?= timeAgo($report['created_at']) ?></div>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if(empty($reports)): ?>
      <p style="color:var(--text-muted);text-align:center;padding:2rem">No reports for this leader yet.</p>
    <?php endif; ?>
  </div>

  <!-- Background Tab -->
  <div id="tab-background-content" style="display:none">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem">
      <div class="glass-card" style="padding:1.5rem">
        <h3 style="font-size:.9rem;font-weight:700;margin-bottom:1rem;color:var(--text-secondary)"><i class="fas fa-id-card"></i> Personal Details</h3>
        <?php
          $details = [
            ['Date of Birth', $leader['dob'] ? date('d M Y',strtotime($leader['dob'])) : null],
            ['Gender', $leader['gender']??null],
            ['Email', $leader['email']??null],
          ];
          foreach($details as [$lbl,$val]):
            if(!$val) continue;
        ?>
        <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border-glass);font-size:.85rem">
          <span style="color:var(--text-muted)"><?= $lbl ?></span>
          <span style="color:var(--text-primary);font-weight:600"><?= e($val) ?></span>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="glass-card" style="padding:1.5rem">
        <h3 style="font-size:.9rem;font-weight:700;margin-bottom:1rem;color:var(--text-secondary)"><i class="fas fa-gavel"></i> Legal & Assets</h3>
        <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border-glass);font-size:.85rem">
          <span style="color:var(--text-muted)">Criminal Cases</span>
          <span style="color:<?= ($leader['criminal_cases']??0)>0?'#ef4444':'#22c55e' ?>;font-weight:700">
            <?= $leader['criminal_cases']??0 ?>
          </span>
        </div>
        <?php if($leader['assets_declared']??0): ?>
        <div style="display:flex;justify-content:space-between;padding:.5rem 0;font-size:.85rem">
          <span style="color:var(--text-muted)">Assets Declared</span>
          <span style="color:var(--text-primary);font-weight:700">₹<?= number_format($leader['assets_declared']/1e7,2) ?> Cr</span>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
function showTab(name) {
  document.querySelectorAll('[id$="-content"]').forEach(el=>{
    if(el.id.startsWith('tab-')) el.style.display='none';
  });
  document.getElementById('tab-'+name+'-content').style.display='';
  document.querySelectorAll('.profile-tab').forEach(btn=>{
    btn.style.color='var(--text-muted)';
    btn.style.borderBottomColor='transparent';
  });
  const active = document.getElementById('tab-'+name);
  if(active){active.style.color='var(--color-primary)';active.style.borderBottomColor='var(--color-primary)';}
}
showTab('promises');
</script>
