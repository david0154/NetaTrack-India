<?php
/*
 * Variables: $topLeaders, $promiseStats, $projectStats, $reportStats,
 *             $leaderStats, $recentReports, $siteName
 */
?>

<!-- HERO -->
<section class="hero">
  <div class="hero-bg"></div>
  <div class="hero-content">
    <div class="hero-badge">
      <span class="dot"></span>
      Live Political Accountability Platform
    </div>
    <h1>
      Track India’s<br>
      <span class="highlight">Political Leaders</span><br>
      With Full Transparency
    </h1>
    <p>
      Monitor promises, projects, corruption allegations and performance scores
      for every politician across all 36 states and UTs of India — powered by AI.
    </p>
    <div class="hero-cta">
      <a href="<?= url('leaders') ?>" class="btn-hero btn-hero-primary">
        <i class="fas fa-user-tie"></i> Explore Leaders
      </a>
      <a href="<?= url('submit-report') ?>" class="btn-hero btn-hero-ghost">
        <i class="fas fa-flag"></i> Submit Report
      </a>
    </div>

    <div class="hero-stats">
      <div class="hstat-item">
        <div class="hstat-value" data-target="<?= $leaderStats['total']??0 ?>">0</div>
        <div class="hstat-label">Leaders Tracked</div>
      </div>
      <div class="hstat-item">
        <div class="hstat-value" data-target="<?= $promiseStats['total']??0 ?>">0</div>
        <div class="hstat-label">Promises Logged</div>
      </div>
      <div class="hstat-item">
        <div class="hstat-value" data-target="<?= $projectStats['total']??0 ?>">0</div>
        <div class="hstat-label">Projects Monitored</div>
      </div>
      <div class="hstat-item">
        <div class="hstat-value" data-target="<?= $reportStats['total']??0 ?>">0</div>
        <div class="hstat-label">Public Reports</div>
      </div>
      <div class="hstat-item">
        <div class="hstat-value">36</div>
        <div class="hstat-label">States & UTs</div>
      </div>
    </div>
  </div>
</section>

<!-- TOP LEADERS -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <div class="section-label"><i class="fas fa-trophy"></i> Top Performers</div>
      <h2 class="section-title">Highest Rated Leaders</h2>
      <p class="section-subtitle">AI-verified scores based on promises kept, projects delivered, and public trust</p>
    </div>
    <div class="grid-leaders">
      <?php foreach($topLeaders as $leader): ?>
      <?php
        $score = $leader['total_score'];
        $scoreClass = $score>=90?'excellent':($score>=75?'good':($score>=50?'average':'poor'));
        $barColor = $score>=90?'#22c55e':($score>=75?'#06b6d4':($score>=50?'#f59e0b':'#ef4444'));
      ?>
      <a href="<?= url('leaders/'.$leader['slug']) ?>" class="card-glass leader-card" style="text-decoration:none;color:inherit">
        <div class="lc-header">
          <?php if(!empty($leader['photo'])): ?>
            <img src="<?= e($leader['photo']) ?>" class="lc-avatar" alt="<?= e($leader['name']) ?>">
          <?php else: ?>
            <div class="lc-avatar-placeholder"><?= strtoupper(substr($leader['name'],0,1)) ?></div>
          <?php endif; ?>
          <div>
            <div class="lc-name"><?= e($leader['name']) ?></div>
            <div class="lc-meta"><?= e(truncate($leader['designation']??'',40)) ?></div>
            <?php if(!empty($leader['party_name'])): ?>
            <div class="lc-party">
              <span style="width:8px;height:8px;border-radius:50%;background:<?= e($leader['party_color']??'#3b82f6') ?>;display:inline-block"></span>
              <?= e($leader['party_name']) ?>
            </div>
            <?php endif; ?>
          </div>
        </div>
        <?php if(!empty($leader['state_name'])): ?>
        <div style="margin-bottom:.75rem">
          <span class="badge badge-muted"><i class="fas fa-map-marker-alt"></i> <?= e($leader['state_name']) ?></span>
          <?php if($leader['is_verified']??0): ?>
            <span class="badge badge-info" style="margin-left:.35rem"><i class="fas fa-check"></i> Verified</span>
          <?php endif; ?>
        </div>
        <?php endif; ?>
        <div class="lc-score">
          <div>
            <div class="lc-score-val score-<?= $scoreClass ?>"><?= $score ?>/100</div>
            <div class="lc-bar" style="width:80px">
              <div class="lc-bar-fill" style="width:<?= $score ?>%;background:<?= $barColor ?>"></div>
            </div>
          </div>
          <span class="badge badge-<?= $scoreClass==='excellent'?'success':($scoreClass==='good'?'info':($scoreClass==='average'?'warning':'danger')) ?>">
            <?= ucfirst($scoreClass) ?>
          </span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-3">
      <a href="<?= url('leaders') ?>" class="btn btn-ghost">View All Leaders <i class="fas fa-arrow-right"></i></a>
    </div>
  </div>
</section>

<!-- PLATFORM STATS BAR -->
<section style="background:var(--bg2);padding:2.5rem 0;border-top:1px solid var(--border);border-bottom:1px solid var(--border)">
  <div class="container">
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1.5rem;text-align:center">
      <?php
        $cards = [
          ['Promise Completion Rate', ($promiseStats['completion_rate']??0).'%', 'fas fa-handshake', '#22c55e'],
          ['Projects On Time',        ($projectStats['on_time_pct']??0).'%',     'fas fa-check-circle','#06b6d4'],
          ['Reports Verified',        ($reportStats['verified']??0),             'fas fa-shield-alt', '#8b5cf6'],
          ['Avg Leader Score',        ($leaderStats['avg_score']??0),            'fas fa-star',       '#f59e0b'],
        ];
      ?>
      <?php foreach($cards as [$label,$val,$icon,$color]): ?>
      <div>
        <div style="font-size:1.8rem;font-weight:900;color:<?= $color ?>"><?= e($val) ?></div>
        <div style="font-size:.78rem;color:var(--t3);margin-top:.25rem">
          <i class="<?= $icon ?>" style="color:<?= $color ?>;margin-right:.3rem"></i><?= e($label) ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="section section-alt">
  <div class="container">
    <div class="section-header">
      <div class="section-label"><i class="fas fa-cogs"></i> How It Works</div>
      <h2 class="section-title">Powered by AI &amp; Community</h2>
    </div>
    <div class="grid-3" style="gap:2rem">
      <?php
        $steps = [
          ['fas fa-robot','#3b82f6','AI Data Collection',
           'Our AI scrapes news from 50+ Indian news sources daily, extracting promises, project updates and corruption reports automatically.'],
          ['fas fa-shield-alt','#22c55e','Fact Verification',
           'Every claim is cross-verified using AI against multiple sources, PIB releases and official government data portals.'],
          ['fas fa-chart-bar','#8b5cf6','Transparent Scoring',
           'Leaders receive a composite accountability score based on 8 weighted metrics including promise fulfillment and project delivery.'],
        ];
      ?>
      <?php foreach($steps as [$icon,$color,$title,$desc]): ?>
      <div class="card-glass" style="padding:2rem;text-align:center">
        <div style="width:64px;height:64px;border-radius:16px;background:<?= $color ?>1a;border:1px solid <?= $color ?>33;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem">
          <i class="<?= $icon ?>" style="font-size:1.6rem;color:<?= $color ?>"></i>
        </div>
        <h3 style="font-size:1rem;font-weight:700;margin-bottom:.6rem"><?= $title ?></h3>
        <p style="font-size:.85rem;color:var(--t2);line-height:1.7"><?= $desc ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- RECENT REPORTS -->
<?php if(!empty($recentReports)): ?>
<section class="section">
  <div class="container">
    <div class="section-header">
      <div class="section-label"><i class="fas fa-flag"></i> Community Reports</div>
      <h2 class="section-title">Latest Verified Reports</h2>
    </div>
    <div style="display:flex;flex-direction:column;gap:.75rem;max-width:780px;margin:0 auto">
      <?php foreach($recentReports as $report): ?>
      <?php
        $typeColors=['corruption'=>'danger','fake_claim'=>'warning','project_delay'=>'purple',
                     'promise_broken'=>'saffron','positive'=>'success','other'=>'muted'];
      ?>
      <div class="card-glass" style="padding:1rem 1.25rem;display:flex;align-items:center;gap:1rem">
        <span class="badge badge-<?= $typeColors[$report['type']]??'muted' ?>"><?= e(str_replace('_',' ',$report['type'])) ?></span>
        <div style="flex:1;min-width:0">
          <div style="font-size:.9rem;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--t1)"><?= e($report['title']) ?></div>
          <?php if(!empty($report['leader_name'])): ?>
            <div style="font-size:.75rem;color:var(--t3);"><i class="fas fa-user-tie"></i> <?= e($report['leader_name']) ?> • <?= timeAgo($report['created_at']) ?></div>
          <?php endif; ?>
        </div>
        <span class="badge badge-success"><i class="fas fa-check"></i> Verified</span>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-3">
      <a href="<?= url('submit-report') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Submit Your Report</a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- CTA BANNER -->
<section style="padding:5rem 0;background:linear-gradient(135deg,rgba(59,130,246,0.1),rgba(139,92,246,0.1));border-top:1px solid var(--border);border-bottom:1px solid var(--border)">
  <div class="container text-center">
    <h2 style="font-size:2rem;font-weight:800;margin-bottom:.75rem">Hold Your Representatives Accountable</h2>
    <p style="color:var(--t2);margin-bottom:2rem;font-size:1rem">Every citizen has the right to know what their elected leaders are doing.</p>
    <div style="display:flex;align-items:center;justify-content:center;gap:1rem;flex-wrap:wrap">
      <a href="<?= url('leaders') ?>"      class="btn btn-primary" style="padding:.85rem 2rem"><i class="fas fa-users"></i> Browse All Leaders</a>
      <a href="<?= url('corruption') ?>"   class="btn btn-ghost"   style="padding:.85rem 2rem"><i class="fas fa-exclamation-triangle"></i> Corruption Index</a>
    </div>
  </div>
</section>

<!-- Counter animation -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  const counters = document.querySelectorAll('.hstat-value[data-target]');
  counters.forEach(el => {
    const target = parseInt(el.dataset.target) || 0;
    if(!target){el.textContent='0';return;}
    let current = 0;
    const step = Math.ceil(target / 60);
    const timer = setInterval(() => {
      current = Math.min(current + step, target);
      el.textContent = current.toLocaleString('en-IN');
      if(current >= target) clearInterval(timer);
    }, 20);
  });
});
</script>
