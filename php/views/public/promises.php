<?php
// Variables: $promises (paginated), $states, $leaders, $stats
$page_title     = 'Promise Tracker';
$page_meta_desc = 'Track election and governance promises by Indian political leaders. AI-verified.';
?>
<div class="section">
  <div class="section-inner">
    <div class="section-header">
      <div class="section-badge"><i class="fas fa-handshake"></i> Promise Tracker</div>
      <h1 class="section-title">Political <span class="hl">Promises</span></h1>
      <p class="section-desc">Every promise made, tracked in real-time against actual delivery.</p>
    </div>

    <!-- Promise Stats -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:2.5rem">
      <?php
        $pStats = [
          ['Kept',        $stats['kept']??0,      'success','#22c55e'],
          ['Broken',      $stats['broken']??0,    'danger', '#ef4444'],
          ['In Progress', $stats['in_progress']??0,'info',  '#06b6d4'],
          ['Total',       $stats['total']??0,     'muted',  '#8b5cf6'],
        ];
        foreach($pStats as [$lbl,$val,$badge,$color]):
      ?>
      <div class="glass-card" style="padding:1.25rem;text-align:center">
        <div style="font-size:2rem;font-weight:900;color:<?= $color ?>;margin-bottom:.25rem"><?= number_format($val) ?></div>
        <div style="font-size:.78rem;color:var(--text-muted)"><?= $lbl ?></div>
        <?php if($stats['total']??0): ?>
          <div style="margin-top:.5rem;font-size:.7rem;color:<?= $color ?>">
            <?= round(($val/($stats['total']??1))*100) ?>%
          </div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Filters -->
    <form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:2rem">
      <input type="text" name="q" value="<?= e($_GET['q']??'') ?>" class="form-control-pub"
        style="flex:1;min-width:200px" placeholder="🔍 Search promises...">
      <select name="status" class="form-control-pub" style="max-width:160px">
        <option value="">All Statuses</option>
        <?php foreach(['kept','broken','in_progress','partial','expired'] as $s): ?>
          <option value="<?= $s ?>" <?= ($_GET['status']??'')===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
        <?php endforeach; ?>
      </select>
      <select name="leader_id" class="form-control-pub" style="max-width:200px">
        <option value="">All Leaders</option>
        <?php foreach($leaders??[] as $l): ?>
          <option value="<?= $l['id'] ?>" <?= ($_GET['leader_id']??'')==$l['id']?'selected':'' ?>><?= e($l['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn-hero-primary" style="padding:.65rem 1.25rem">
        <i class="fas fa-search"></i> Filter
      </button>
    </form>

    <!-- Promises List -->
    <div class="promises-grid">
      <?php if(empty($promises['data'])): ?>
        <div style="grid-column:1/-1;text-align:center;padding:4rem;color:var(--text-muted)">
          <i class="fas fa-handshake" style="font-size:3rem;margin-bottom:1rem;display:block;opacity:.3"></i>
          No promises found.
        </div>
      <?php else: ?>
      <?php
        $sc=['kept'=>'success','broken'=>'danger','in_progress'=>'info','partial'=>'warning','expired'=>'muted'];
        $bc=['kept'=>'#22c55e','broken'=>'#ef4444','in_progress'=>'#06b6d4','partial'=>'#f59e0b','expired'=>'#64748b'];
        foreach($promises['data'] as $p):
      ?>
      <div class="glass-card promise-card" style="--promise-color:<?= $bc[$p['status']]??'#64748b' ?>">
        <div class="promise-title"><?= e($p['title']) ?></div>
        <div class="promise-meta">
          <span><i class="fas fa-user-tie"></i>
            <a href="<?= url('leaders/'.($p['leader_slug']??$p['leader_id'])) ?>" style="color:var(--color-primary)">
              <?= e($p['leader_name']??'Unknown') ?>
            </a>
          </span>
          <span><i class="fas fa-map-marker-alt"></i> <?= e($p['state_name']??'India') ?></span>
          <?php if($p['deadline']): ?>
            <span><i class="fas fa-clock"></i> <?= date('d M Y',strtotime($p['deadline'])) ?></span>
          <?php endif; ?>
        </div>
        <?php if($p['description']): ?>
          <p style="font-size:.82rem;color:var(--text-muted);margin-bottom:.75rem;line-height:1.6">
            <?= e(truncate($p['description'],160)) ?>
          </p>
        <?php endif; ?>
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.4rem">
          <span class="badge badge-<?= $sc[$p['status']]??'muted' ?>"><?= e(str_replace('_',' ',$p['status'])) ?></span>
          <div style="display:flex;gap:.4rem">
            <?php if($p['category']): ?>
              <span class="badge badge-muted"><?= e($p['category']) ?></span>
            <?php endif; ?>
            <?php if($p['ai_confidence']??0): ?>
              <span style="font-size:.7rem;color:var(--text-muted)"><i class="fas fa-robot"></i> <?= $p['ai_confidence'] ?>%</span>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if(($promises['last_page']??1)>1): ?>
    <div class="pagination">
      <?php for($pg=1;$pg<=$promises['last_page'];$pg++): ?>
        <a href="?<?= http_build_query(array_merge($_GET,['page'=>$pg])) ?>"
           class="page-btn <?= $pg==$promises['current_page']?'active':'' ?>"><?= $pg ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
