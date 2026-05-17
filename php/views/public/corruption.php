<?php
// Variables: $leaders (ranked by corruption), $stats, $states
$page_title     = 'Corruption Index';
$page_meta_desc = 'India\'s political corruption index — AI-verified reports, fake claim detection and accountability rankings.';
?>
<div class="section">
  <div class="section-inner">
    <div class="section-header">
      <div class="section-badge" style="background:rgba(239,68,68,.1);border-color:rgba(239,68,68,.2);color:#ef4444">
        <i class="fas fa-exclamation-triangle"></i> Corruption Index
      </div>
      <h1 class="section-title">Political <span style="background:linear-gradient(135deg,#ef4444,#f97316);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">Corruption Index</span></h1>
      <p class="section-desc">AI-powered corruption detection. Data sourced from public reports, court records and news analysis.</p>
    </div>

    <!-- Corruption Stats -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:2.5rem">
      <?php
        $cStats=[
          ['Total Reports',       $stats['total_reports']??0,      '#ef4444','fas fa-flag'],
          ['Fake Claims Detected',$stats['fake_detected']??0,      '#f59e0b','fas fa-robot'],
          ['Leaders with Cases',  $stats['leaders_with_cases']??0, '#8b5cf6','fas fa-user-times'],
          ['Resolved Cases',      $stats['resolved']??0,           '#22c55e','fas fa-check-shield'],
        ];
        foreach($cStats as [$lbl,$val,$color,$icon]):
      ?>
      <div class="glass-card" style="padding:1.25rem;text-align:center;border-top:3px solid <?= $color ?>">
        <div style="font-size:1.8rem;font-weight:900;color:<?= $color ?>;margin-bottom:.25rem"><?= number_format($val) ?></div>
        <div style="font-size:.78rem;color:var(--text-muted)"><?= $lbl ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Filters -->
    <form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1.5rem">
      <select name="state_id" class="form-control-pub" style="max-width:200px">
        <option value="">All States</option>
        <?php foreach($states??[] as $st): ?>
          <option value="<?= $st['id'] ?>" <?= ($_GET['state_id']??'')==$st['id']?'selected':'' ?>><?= e($st['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="sort" class="form-control-pub" style="max-width:200px">
        <option value="cases" <?= ($_GET['sort']??'cases')==='cases'?'selected':'' ?>>Most Criminal Cases</option>
        <option value="reports" <?= ($_GET['sort']??'')==='reports'?'selected':'' ?>>Most Reports</option>
        <option value="score" <?= ($_GET['sort']??'')==='score'?'selected':'' ?>>Lowest Score</option>
      </select>
      <button type="submit" class="btn-hero-primary" style="padding:.65rem 1.25rem;background:linear-gradient(135deg,#ef4444,#dc2626)">
        <i class="fas fa-filter"></i> Filter
      </button>
    </form>

    <!-- Corruption Table -->
    <div class="glass-card" style="overflow:hidden">
      <div style="overflow-x:auto">
        <table class="corruption-table">
          <thead>
            <tr>
              <th>Rank</th>
              <th>Leader</th>
              <th>Party / State</th>
              <th>Criminal Cases</th>
              <th>Corruption Reports</th>
              <th>Fake Claims</th>
              <th>Score</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if(empty($leaders)): ?>
            <tr><td colspan="8" style="text-align:center;padding:3rem;color:var(--text-muted)">No data available.</td></tr>
            <?php else: ?>
            <?php foreach($leaders as $i => $leader): ?>
            <tr>
              <td>
                <?php if($i===0): ?>
                  <span style="color:#ef4444;font-weight:800;font-size:1.1rem">#1</span>
                <?php elseif($i===1): ?>
                  <span style="color:#f97316;font-weight:800">#2</span>
                <?php elseif($i===2): ?>
                  <span style="color:#f59e0b;font-weight:800">#3</span>
                <?php else: ?>
                  <span style="color:var(--text-muted)">#<?= $i+1 ?></span>
                <?php endif; ?>
              </td>
              <td>
                <div style="display:flex;align-items:center;gap:.65rem">
                  <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#ef4444,#8b5cf6);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;color:#fff;flex-shrink:0">
                    <?= strtoupper(substr($leader['name'],0,1)) ?>
                  </div>
                  <div>
                    <a href="<?= url('leaders/'.($leader['slug']??$leader['id'])) ?>" style="font-weight:700;font-size:.875rem;color:var(--text-primary)">
                      <?= e($leader['name']) ?>
                    </a>
                    <?php if($leader['is_verified']??0): ?>
                      <span class="badge badge-info" style="font-size:.55rem;margin-left:.25rem">Verified</span>
                    <?php endif; ?>
                  </div>
                </div>
              </td>
              <td style="font-size:.82rem">
                <span style="color:<?= e($leader['party_color']??'#3b82f6') ?>">●</span>
                <?= e($leader['party_name']??'Ind') ?><br>
                <span style="color:var(--text-muted);font-size:.75rem"><?= e($leader['state_name']??'—') ?></span>
              </td>
              <td style="text-align:center">
                <span style="font-size:1.1rem;font-weight:800;color:<?= ($leader['criminal_cases']??0)>0?'#ef4444':'#22c55e' ?>">
                  <?= $leader['criminal_cases']??0 ?>
                </span>
              </td>
              <td style="text-align:center">
                <span style="font-weight:700;color:<?= ($leader['corruption_reports']??0)>5?'#ef4444':($leader['corruption_reports']??0)>0?'#f59e0b':'#22c55e' ?>">
                  <?= $leader['corruption_reports']??0 ?>
                </span>
              </td>
              <td style="text-align:center">
                <span style="font-weight:700;color:<?= ($leader['fake_claims']??0)>0?'#f59e0b':'#22c55e' ?>">
                  <?= $leader['fake_claims']??0 ?>
                </span>
              </td>
              <td>
                <?php
                  $score = $leader['total_score']??50;
                  $sc = $score>=90?'success':($score>=75?'info':($score>=50?'warning':'danger'));
                ?>
                <span class="badge badge-<?= $sc ?>" style="font-size:.8rem;font-weight:800"><?= $score ?></span>
              </td>
              <td>
                <a href="<?= url('report?leader_id='.$leader['id']) ?>" class="btn-nav btn-nav-outline" style="font-size:.75rem;color:#ef4444;border-color:#ef444433">
                  <i class="fas fa-flag"></i> Report
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Info Box -->
    <div style="margin-top:2rem;background:rgba(239,68,68,.06);border:1px solid rgba(239,68,68,.15);border-radius:var(--radius);padding:1.5rem">
      <h3 style="font-size:.9rem;font-weight:700;color:#ef4444;margin-bottom:.6rem">
        <i class="fas fa-info-circle"></i> How the Corruption Index Works
      </h3>
      <p style="font-size:.83rem;color:var(--text-muted);line-height:1.7">
        The index is calculated using: <strong style="color:var(--text-secondary)">criminal cases filed</strong>,
        <strong style="color:var(--text-secondary)">citizen corruption reports</strong> (AI-verified),
        <strong style="color:var(--text-secondary)">fake/misleading claims detected</strong> by our AI,
        and <strong style="color:var(--text-secondary)">news mentions</strong> from verified sources.
        Data is updated every 6 hours. We do not make legal determinations — this is informational only.
      </p>
    </div>
  </div>
</div>
