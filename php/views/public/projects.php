<?php
// Variables: $projects (paginated), $states, $stats
$page_title     = 'Project Monitor';
$page_meta_desc = 'Track government projects across India — completion status, budget efficiency and delays.';
?>
<div class="section">
  <div class="section-inner">
    <div class="section-header">
      <div class="section-badge"><i class="fas fa-project-diagram"></i> Project Monitor</div>
      <h1 class="section-title">Government <span class="hl">Projects</span></h1>
      <p class="section-desc">Real-time monitoring of infrastructure and development projects across all states.</p>
    </div>

    <!-- Stats -->
    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:1rem;margin-bottom:2.5rem">
      <?php
        $pCards=[
          ['Total',       $stats['total']??0,       '#8b5cf6'],
          ['Completed',   $stats['completed']??0,   '#22c55e'],
          ['In Progress', $stats['in_progress']??0, '#06b6d4'],
          ['Delayed',     $stats['delayed']??0,     '#ef4444'],
          ['Budget (Cr)', '₹'.number_format($stats['total_budget']??0,0), '#f97316'],
        ];
        foreach($pCards as [$lbl,$val,$color]):
      ?>
      <div class="glass-card" style="padding:1.25rem;text-align:center">
        <div style="font-size:<?= is_string($val)&&str_starts_with($val,'₹')?'1.3rem':'1.9rem' ?>;font-weight:900;color:<?= $color ?>;margin-bottom:.25rem"><?= $val ?></div>
        <div style="font-size:.78rem;color:var(--text-muted)"><?= $lbl ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Filters -->
    <form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:2rem">
      <input type="text" name="q" value="<?= e($_GET['q']??'') ?>" class="form-control-pub"
        style="flex:1;min-width:200px" placeholder="🔍 Search projects...">
      <select name="status" class="form-control-pub" style="max-width:160px">
        <option value="">All Statuses</option>
        <?php foreach(['planned','in_progress','completed','delayed','cancelled'] as $s): ?>
          <option value="<?= $s ?>" <?= ($_GET['status']??'')===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="state_id" class="form-control-pub" style="max-width:160px">
        <option value="">All States</option>
        <?php foreach($states??[] as $st): ?>
          <option value="<?= $st['id'] ?>" <?= ($_GET['state_id']??'')==$st['id']?'selected':'' ?>><?= e($st['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn-hero-primary" style="padding:.65rem 1.25rem">
        <i class="fas fa-search"></i> Filter
      </button>
    </form>

    <!-- Projects Grid -->
    <div class="grid-auto">
      <?php if(empty($projects['data'])): ?>
        <div style="grid-column:1/-1;text-align:center;padding:4rem;color:var(--text-muted)">
          <i class="fas fa-hard-hat" style="font-size:3rem;margin-bottom:1rem;display:block;opacity:.3"></i>
          No projects found.
        </div>
      <?php else: ?>
      <?php
        $sc=['completed'=>'success','in_progress'=>'info','planned'=>'muted','delayed'=>'danger','cancelled'=>'warning'];
        $cc=['completed'=>'#22c55e','in_progress'=>'#06b6d4','planned'=>'#8b5cf6','delayed'=>'#ef4444','cancelled'=>'#f59e0b'];
        foreach($projects['data'] as $proj):
          $status = $proj['status']??'planned';
      ?>
      <div class="glass-card" style="padding:1.5rem;border-top:3px solid <?= $cc[$status]??'#8b5cf6' ?>">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.75rem;gap:.5rem">
          <span class="badge badge-<?= $sc[$status]??'muted' ?>"><?= e(str_replace('_',' ',$status)) ?></span>
          <?php if($proj['budget_crore']??0): ?>
            <span style="font-size:.82rem;font-weight:700;color:var(--text-secondary)">₹<?= number_format($proj['budget_crore'],0) ?> Cr</span>
          <?php endif; ?>
        </div>
        <h3 style="font-size:.95rem;font-weight:700;color:var(--text-primary);margin-bottom:.4rem;line-height:1.4">
          <?= e($proj['title']) ?>
        </h3>
        <div style="font-size:.8rem;color:var(--text-muted);margin-bottom:.75rem;display:flex;gap:1rem;flex-wrap:wrap">
          <?php if($proj['leader_name']??null): ?>
            <span><i class="fas fa-user-tie"></i>
              <a href="<?= url('leaders/'.($proj['leader_slug']??$proj['leader_id'])) ?>" style="color:var(--color-primary)">
                <?= e($proj['leader_name']) ?>
              </a>
            </span>
          <?php endif; ?>
          <?php if($proj['state_name']??null): ?>
            <span><i class="fas fa-map-marker-alt"></i> <?= e($proj['state_name']) ?></span>
          <?php endif; ?>
          <?php if($proj['deadline']??null): ?>
            <span style="color:<?= ($status==='delayed')?'#ef4444':'inherit' ?>">
              <i class="fas fa-calendar"></i> <?= date('M Y',strtotime($proj['deadline'])) ?>
            </span>
          <?php endif; ?>
        </div>
        <?php if($proj['description']??null): ?>
          <p style="font-size:.82rem;color:var(--text-muted);margin-bottom:.75rem;line-height:1.6">
            <?= e(truncate($proj['description'],130)) ?>
          </p>
        <?php endif; ?>
        <div style="font-size:.72rem;color:var(--text-muted);margin-bottom:.35rem">Completion: <?= $proj['completion_pct']??0 ?>%</div>
        <div class="score-bar-h">
          <div class="score-bar-fill <?= $status==='delayed'?'danger':($status==='completed'?'success':'') ?>"
            style="width:<?= $proj['completion_pct']??0 ?>%"></div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if(($projects['last_page']??1)>1): ?>
    <div class="pagination">
      <?php for($pg=1;$pg<=$projects['last_page'];$pg++): ?>
        <a href="?<?= http_build_query(array_merge($_GET,['page'=>$pg])) ?>"
           class="page-btn <?= $pg==$projects['current_page']?'active':'' ?>"><?= $pg ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
