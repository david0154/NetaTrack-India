<?php
// Variables: $leaders (paginated), $states, $parties, $filters
$page_title     = 'Leaders';
$page_meta_desc = 'Browse political leaders across India. View accountability scores, promises and project records.';
?>

<!-- Page Header -->
<div class="section" style="padding-bottom:1rem">
  <div class="section-inner">
    <div class="section-header" style="margin-bottom:1.5rem">
      <div class="section-badge"><i class="fas fa-user-tie"></i> Leader Database</div>
      <h1 class="section-title">India's Political <span class="hl">Leaders</span></h1>
      <p class="section-desc">Comprehensive profiles with AI-computed accountability scores across all states.</p>
    </div>

    <!-- Filters -->
    <form method="GET" style="margin-bottom:2rem">
      <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr auto;gap:.75rem;align-items:end">
        <div>
          <input type="text" name="q" value="<?= e($_GET['q']??'') ?>"
            class="form-control-pub" placeholder="🔍 Search by name, constituency...">
        </div>
        <select name="state" class="form-control-pub">
          <option value="">All States</option>
          <?php foreach($states??[] as $s): ?>
            <option value="<?= $s['slug']??$s['id'] ?>" <?= ($_GET['state']??'')==($s['slug']??$s['id'])?'selected':'' ?>>
              <?= e($s['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <select name="party" class="form-control-pub">
          <option value="">All Parties</option>
          <?php foreach($parties??[] as $p): ?>
            <option value="<?= $p['id'] ?>" <?= ($_GET['party']??'')==$p['id']?'selected':'' ?>>
              <?= e($p['abbreviation']??$p['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <select name="rank" class="form-control-pub">
          <option value="">All Ranks</option>
          <option value="Excellent" <?= ($_GET['rank']??'')==='Excellent'?'selected':'' ?>>Excellent (90+)</option>
          <option value="Good"      <?= ($_GET['rank']??'')==='Good'?'selected':'' ?>>Good (75+)</option>
          <option value="Average"   <?= ($_GET['rank']??'')==='Average'?'selected':'' ?>>Average (50+)</option>
          <option value="Poor"      <?= ($_GET['rank']??'')==='Poor'?'selected':'' ?>>Poor (&lt;50)</option>
        </select>
        <button type="submit" class="btn-hero-primary" style="padding:.65rem 1.25rem">
          <i class="fas fa-search"></i>
        </button>
      </div>
    </form>

    <!-- Sort & Count -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.5rem">
      <div style="font-size:.85rem;color:var(--text-muted)">
        Showing <strong style="color:var(--text-primary)"><?= number_format($leaders['total']??0) ?></strong> leaders
        <?php if(!empty($_GET['q'])): ?> for &quot;<?= e($_GET['q']) ?>&quot;<?php endif; ?>
      </div>
      <form method="GET" style="display:flex;align-items:center;gap:.5rem">
        <?php foreach($_GET as $k=>$v): if($k==='sort') continue; ?>
          <input type="hidden" name="<?= e($k) ?>" value="<?= e($v) ?>">
        <?php endforeach; ?>
        <label style="font-size:.82rem;color:var(--text-muted)">Sort by:</label>
        <select name="sort" class="form-control-pub" style="max-width:160px" onchange="this.form.submit()">
          <option value="score" <?= ($_GET['sort']??'score')==='score'?'selected':'' ?>>Highest Score</option>
          <option value="name"  <?= ($_GET['sort']??'')==='name'?'selected':'' ?>>Name A-Z</option>
          <option value="recent"<?= ($_GET['sort']??'')==='recent'?'selected':'' ?>>Recently Added</option>
        </select>
      </form>
    </div>

    <!-- Leaders Grid -->
    <div class="leaders-grid">
      <?php if(empty($leaders['data'])): ?>
        <div style="grid-column:1/-1;text-align:center;padding:4rem;color:var(--text-muted)">
          <i class="fas fa-user-slash" style="font-size:3rem;margin-bottom:1rem;display:block;opacity:.3"></i>
          No leaders found. Try different filters.
        </div>
      <?php else: ?>
      <?php foreach($leaders['data'] as $leader): ?>
      <?php
        $rank      = $leader['score_rank']??'Average';
        $ringClass = strtolower($rank);
        $badgeMap  = ['excellent'=>'success','good'=>'info','average'=>'warning','poor'=>'danger'];
      ?>
      <a href="<?= url('leaders/'.($leader['slug']??$leader['id'])) ?>" style="text-decoration:none">
        <div class="glass-card leader-card" style="--party-color:<?= e($leader['party_color']??'#3b82f6') ?>">
          <?php if($leader['is_verified']??0): ?>
            <div style="position:absolute;top:.75rem;right:.75rem">
              <span class="badge badge-info" style="font-size:.6rem"><i class="fas fa-check-circle"></i> Verified</span>
            </div>
          <?php endif; ?>
          <div class="leader-avatar-lg">
            <?php if($leader['photo']): ?>
              <img src="<?= e($leader['photo']) ?>" style="width:100%;height:100%;border-radius:50%;object-fit:cover" loading="lazy">
            <?php else: ?>
              <?= strtoupper(substr($leader['name'],0,1)) ?>
            <?php endif; ?>
          </div>
          <div class="score-ring <?= $ringClass ?>"><?= $leader['total_score'] ?></div>
          <div class="leader-name"><?= e($leader['name']) ?></div>
          <div class="leader-party">
            <span style="color:<?= e($leader['party_color']??'#3b82f6') ?>">●</span>
            <?= e($leader['party_name']??'Independent') ?> &bull; <?= e($leader['state_name']??'India') ?>
          </div>
          <div style="font-size:.77rem;color:var(--text-muted);margin-bottom:.75rem"><?= e(truncate($leader['designation']??'',35)) ?></div>
          <span class="badge badge-<?= $badgeMap[$ringClass]??'muted' ?>"><?= $rank ?></span>
          <?php if($leader['criminal_cases']??0): ?>
            <span class="badge badge-danger" style="margin-left:.25rem">
              <i class="fas fa-gavel"></i> <?= $leader['criminal_cases'] ?> case<?= $leader['criminal_cases']>1?'s':'' ?>
            </span>
          <?php endif; ?>
        </div>
      </a>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if(($leaders['last_page']??1) > 1): ?>
    <div class="pagination">
      <?php if($leaders['current_page']>1): ?>
        <a href="?<?= http_build_query(array_merge($_GET,['page'=>$leaders['current_page']-1])) ?>" class="page-btn">
          <i class="fas fa-chevron-left"></i>
        </a>
      <?php endif; ?>
      <?php
        $start = max(1,$leaders['current_page']-2);
        $end   = min($leaders['last_page'],$leaders['current_page']+2);
        for($p=$start;$p<=$end;$p++):
      ?>
        <a href="?<?= http_build_query(array_merge($_GET,['page'=>$p])) ?>"
           class="page-btn <?= $p==$leaders['current_page']?'active':'' ?>"><?= $p ?></a>
      <?php endfor; ?>
      <?php if($leaders['current_page']<$leaders['last_page']): ?>
        <a href="?<?= http_build_query(array_merge($_GET,['page'=>$leaders['current_page']+1])) ?>" class="page-btn">
          <i class="fas fa-chevron-right"></i>
        </a>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
