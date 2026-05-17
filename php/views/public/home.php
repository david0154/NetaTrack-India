<?php
// Variables: $stats, $topLeaders, $recentPromises, $recentProjects, $recentReports
$page_title = null; // uses site name directly
?>

<!-- HERO -->
<section class="hero">
  <div class="hero-inner">
    <div class="hero-content">
      <div class="hero-badge">
        <i class="fas fa-shield-alt"></i> India's #1 Political Accountability Platform
      </div>
      <h1 class="hero-title">
        Track Your <span class="hl">Neta's</span><br>Promises &amp; Projects
      </h1>
      <p class="hero-desc">
        Real-time tracking of political leaders across all 36 states &amp; UTs.
        Verify promises, monitor projects, expose corruption — all AI-powered.
      </p>
      <div class="hero-actions">
        <a href="<?= url('leaders') ?>" class="btn-hero-primary">
          <i class="fas fa-search"></i> Explore Leaders
        </a>
        <a href="<?= url('report') ?>" class="btn-hero-outline">
          <i class="fas fa-flag"></i> Report Corruption
        </a>
      </div>
      <div class="hero-stats">
        <div class="hero-stat">
          <div class="val"><?= number_format($stats['leaders']??0) ?>+</div>
          <div class="lbl">Leaders Tracked</div>
        </div>
        <div class="hero-stat">
          <div class="val"><?= number_format($stats['promises']??0) ?>+</div>
          <div class="lbl">Promises Monitored</div>
        </div>
        <div class="hero-stat">
          <div class="val"><?= number_format($stats['projects']??0) ?>+</div>
          <div class="lbl">Projects Tracked</div>
        </div>
        <div class="hero-stat">
          <div class="val"><?= number_format($stats['reports']??0) ?>+</div>
          <div class="lbl">Public Reports</div>
        </div>
      </div>
    </div>

    <!-- India Map -->
    <div class="hero-map-panel">
      <h3><i class="fas fa-map" style="color:var(--color-saffron)"></i> State Accountability Heatmap</h3>
      <div style="background:rgba(59,130,246,0.05);border-radius:10px;padding:1rem;text-align:center">
        <svg id="indiaMapSvg" viewBox="0 0 400 460" style="max-width:100%;height:300px">
          <!-- Simplified India map outline with major states -->
          <!-- Jammu & Kashmir -->
          <path class="heat-low" d="M130,20 L180,15 L200,35 L190,55 L160,60 L140,50 Z" data-state="Jammu &amp; Kashmir"/>
          <!-- Himachal Pradesh -->
          <path class="heat-med" d="M160,60 L190,55 L200,75 L175,85 L155,80 Z" data-state="Himachal Pradesh"/>
          <!-- Punjab -->
          <path class="heat-low" d="M130,65 L160,60 L155,80 L135,85 L118,75 Z" data-state="Punjab"/>
          <!-- Haryana -->
          <path class="heat-med" d="M135,85 L155,80 L165,100 L145,110 L128,100 Z" data-state="Haryana"/>
          <!-- Uttarakhand -->
          <path class="heat-low" d="M175,85 L200,75 L215,95 L200,110 L175,105 Z" data-state="Uttarakhand"/>
          <!-- Uttar Pradesh -->
          <path class="heat-high" d="M145,110 L165,100 L200,110 L230,120 L240,145 L215,160 L180,165 L150,155 L138,135 Z" data-state="Uttar Pradesh"/>
          <!-- Bihar -->
          <path class="heat-high" d="M240,145 L270,140 L285,155 L275,175 L250,180 L230,170 L215,160 Z" data-state="Bihar"/>
          <!-- Rajasthan -->
          <path class="heat-med" d="M90,90 L130,85 L138,135 L130,175 L100,190 L70,170 L65,130 L75,105 Z" data-state="Rajasthan"/>
          <!-- Gujarat -->
          <path class="heat-low" d="M65,170 L100,190 L110,220 L95,250 L65,245 L45,220 L50,190 Z" data-state="Gujarat"/>
          <!-- Madhya Pradesh -->
          <path class="heat-med" d="M130,175 L180,165 L215,160 L230,170 L225,200 L205,220 L175,230 L145,220 L128,205 L125,185 Z" data-state="Madhya Pradesh"/>
          <!-- Maharashtra -->
          <path class="heat-high" d="M110,220 L128,205 L145,220 L175,230 L185,260 L165,280 L140,285 L115,270 L95,250 Z" data-state="Maharashtra"/>
          <!-- Jharkhand -->
          <path class="heat-med" d="M250,180 L275,175 L295,190 L290,215 L265,225 L245,215 L235,200 Z" data-state="Jharkhand"/>
          <!-- West Bengal -->
          <path class="heat-high" d="M285,155 L315,150 L330,170 L325,200 L305,215 L290,215 L295,190 L275,175 Z" data-state="West Bengal"/>
          <!-- Odisha -->
          <path class="heat-med" d="M265,225 L290,215 L305,215 L315,235 L305,260 L280,270 L260,255 L248,235 Z" data-state="Odisha"/>
          <!-- Chhattisgarh -->
          <path class="heat-low" d="M225,200 L245,215 L248,235 L235,260 L215,265 L198,250 L195,225 L205,220 Z" data-state="Chhattisgarh"/>
          <!-- Telangana -->
          <path class="heat-med" d="M175,280 L200,270 L215,265 L225,285 L215,305 L195,315 L175,305 L165,290 Z" data-state="Telangana"/>
          <!-- Andhra Pradesh -->
          <path class="heat-low" d="M175,305 L215,305 L230,320 L235,345 L215,365 L190,370 L168,355 L160,330 Z" data-state="Andhra Pradesh"/>
          <!-- Karnataka -->
          <path class="heat-med" d="M140,285 L165,280 L175,305 L160,330 L145,345 L120,340 L105,315 L110,290 Z" data-state="Karnataka"/>
          <!-- Tamil Nadu -->
          <path class="heat-high" d="M145,345 L160,330 L168,355 L165,385 L148,400 L130,395 L118,370 L120,350 Z" data-state="Tamil Nadu"/>
          <!-- Kerala -->
          <path class="heat-low" d="M105,315 L120,340 L118,370 L108,390 L95,380 L88,355 L92,330 Z" data-state="Kerala"/>
          <!-- Assam -->
          <path class="heat-med" d="M325,155 L355,148 L370,165 L365,185 L340,192 L325,180 L330,170 Z" data-state="Assam"/>
          <!-- Delhi (small) -->
          <circle cx="157" cy="102" r="5" class="heat-high" data-state="Delhi"/>
        </svg>
      </div>
      <div class="map-legend">
        <div class="legend-item"><div class="legend-dot" style="background:#ef4444"></div> High Corruption Reports</div>
        <div class="legend-item"><div class="legend-dot" style="background:#f59e0b"></div> Moderate</div>
        <div class="legend-item"><div class="legend-dot" style="background:#22c55e"></div> Low / Clean</div>
      </div>
      <!-- Map Tooltip -->
      <div id="mapTooltip" style="display:none;position:absolute;background:rgba(0,0,0,.85);color:#fff;padding:.4rem .75rem;border-radius:6px;font-size:.78rem;pointer-events:none;z-index:10"></div>
    </div>
  </div>
</section>

<!-- STATS STRIP -->
<section class="section" style="padding:2rem">
  <div class="container">
    <div class="stats-strip">
      <div class="strip-stat">
        <div class="val" data-counter="<?= $stats['leaders']??0 ?>"><?= $stats['leaders']??0 ?></div>
        <div class="lbl">Political Leaders</div>
        <div class="sub">across all states</div>
      </div>
      <div class="strip-stat">
        <div class="val" data-counter="<?= $stats['promise_kept_pct']??0 ?>"><?= $stats['promise_kept_pct']??0 ?>%</div>
        <div class="lbl">Promise Fulfilment Rate</div>
        <div class="sub"><?= $stats['promises_kept']??0 ?> of <?= $stats['promises']??0 ?> promises</div>
      </div>
      <div class="strip-stat">
        <div class="val" data-counter="<?= $stats['projects']??0 ?>"><?= $stats['projects']??0 ?></div>
        <div class="lbl">Projects Monitored</div>
        <div class="sub"><?= $stats['projects_delayed']??0 ?> delayed</div>
      </div>
      <div class="strip-stat">
        <div class="val" data-counter="<?= $stats['reports']??0 ?>"><?= $stats['reports']??0 ?></div>
        <div class="lbl">Public Reports Submitted</div>
        <div class="sub">AI-verified &amp; reviewed</div>
      </div>
    </div>
  </div>
</section>

<!-- TOP LEADERS -->
<section class="section">
  <div class="section-inner">
    <div class="section-header">
      <div class="section-badge"><i class="fas fa-star"></i> Top Ranked</div>
      <h2 class="section-title">Leaders with <span class="hl">Highest Accountability</span></h2>
      <p class="section-desc">Ranked by promise fulfilment, project delivery, transparency and public satisfaction scores.</p>
    </div>
    <div class="leaders-grid">
      <?php foreach($topLeaders as $leader): ?>
      <?php
        $rank = $leader['score_rank']??'Average';
        $ringClass = strtolower($rank);
      ?>
      <a href="<?= url('leaders/'.$leader['slug']) ?>" style="text-decoration:none">
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
          <div class="leader-party"><?= e($leader['party_name']??'Independent') ?> &bull; <?= e($leader['state_name']??'India') ?></div>
          <div style="font-size:.78rem;color:var(--text-muted);margin-bottom:.75rem"><?= e(truncate($leader['designation']??'',35)) ?></div>
          <span class="badge badge-<?= $ringClass==='excellent'?'success':($ringClass==='good'?'info':($ringClass==='average'?'warning':'danger')) ?>"><?= $rank ?></span>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:2rem">
      <a href="<?= url('leaders') ?>" class="btn-hero-outline">
        View All Leaders <i class="fas fa-arrow-right"></i>
      </a>
    </div>
  </div>
</section>

<!-- PROMISE TRACKER PREVIEW -->
<section class="section" style="background:linear-gradient(180deg,transparent,rgba(59,130,246,0.04),transparent)">
  <div class="section-inner">
    <div class="section-header">
      <div class="section-badge"><i class="fas fa-handshake"></i> Promise Tracker</div>
      <h2 class="section-title">Were <span class="hl">Promises Kept?</span></h2>
      <p class="section-desc">AI-verified tracking of election promises against real-world outcomes.</p>
    </div>
    <div class="promises-grid">
      <?php foreach(array_slice($recentPromises??[],0,6) as $promise): ?>
      <?php
        $statusColors=['kept'=>'success','broken'=>'danger','in_progress'=>'info','partial'=>'warning','expired'=>'muted'];
        $statusColor  = $statusColors[$promise['status']]??'muted';
        $borderColors =['kept'=>'#22c55e','broken'=>'#ef4444','in_progress'=>'#06b6d4','partial'=>'#f59e0b','expired'=>'#64748b'];
        $borderColor  = $borderColors[$promise['status']]??'#64748b';
      ?>
      <div class="glass-card promise-card" style="--promise-color:<?= $borderColor ?>">
        <div class="promise-title"><?= e(truncate($promise['title'],70)) ?></div>
        <div class="promise-meta">
          <span><i class="fas fa-user-tie"></i> <?= e($promise['leader_name']??'Unknown') ?></span>
          <span><i class="fas fa-map-marker-alt"></i> <?= e($promise['state_name']??'India') ?></span>
          <?php if($promise['deadline']): ?>
            <span><i class="fas fa-clock"></i> <?= date('M Y',strtotime($promise['deadline'])) ?></span>
          <?php endif; ?>
        </div>
        <div style="display:flex;align-items:center;justify-content:space-between">
          <span class="badge badge-<?= $statusColor ?>"><?= e(str_replace('_',' ',$promise['status'])) ?></span>
          <?php if($promise['ai_confidence']??0): ?>
            <span style="font-size:.72rem;color:var(--text-muted)">
              <i class="fas fa-robot"></i> <?= $promise['ai_confidence'] ?>% confident
            </span>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:2rem">
      <a href="<?= url('promises') ?>" class="btn-hero-outline">
        Track All Promises <i class="fas fa-arrow-right"></i>
      </a>
    </div>
  </div>
</section>

<!-- HOW IT WORKS -->
<section class="section">
  <div class="section-inner">
    <div class="section-header">
      <div class="section-badge"><i class="fas fa-info-circle"></i> How It Works</div>
      <h2 class="section-title">Transparent. <span class="hl">AI-Powered.</span> People-Driven.</h2>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1.5rem">
      <?php
        $steps = [
          ['fas fa-robot','#3b82f6','AI Data Collection','Our AI scrapes news from 50+ sources daily, extracting facts about leaders, promises and projects automatically.'],
          ['fas fa-check-circle','#22c55e','Fact Verification','Every piece of information is cross-verified using Google Fact Check API and AI analysis for accuracy.'],
          ['fas fa-chart-line','#8b5cf6','Score Calculation','Leaders receive dynamic accountability scores based on promise completion, project delivery and more.'],
          ['fas fa-users','#f97316','Community Reports','Citizens submit reports directly. Our AI + admin team reviews and approves verified submissions.'],
        ];
        foreach($steps as $i => [$icon,$color,$title,$desc]):
      ?>
      <div class="glass-card" style="padding:1.75rem;text-align:center">
        <div style="width:60px;height:60px;border-radius:14px;background:<?= $color ?>1a;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;font-size:1.5rem;color:<?= $color ?>">
          <i class="<?= $icon ?>"></i>
        </div>
        <h3 style="font-size:1rem;font-weight:700;margin-bottom:.5rem">
          <span style="color:<?= $color ?>;font-size:1.2rem;margin-right:.35rem"><?= $i+1 ?>.</span><?= $title ?>
        </h3>
        <p style="font-size:.85rem;color:var(--text-muted);line-height:1.65"><?= $desc ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="section">
  <div class="container">
    <div style="
      background:linear-gradient(135deg,rgba(249,115,22,0.15),rgba(59,130,246,0.15));
      border:1px solid rgba(249,115,22,0.2);
      border-radius:var(--radius-lg);
      padding:3rem;text-align:center;
    ">
      <h2 style="font-size:clamp(1.5rem,3vw,2.2rem);font-weight:800;margin-bottom:.75rem">
        Know Something? <span style="color:var(--color-saffron)">Report It.</span>
      </h2>
      <p style="font-size:1rem;color:var(--text-secondary);max-width:520px;margin:0 auto 1.75rem">
        Your report could expose corruption, hold politicians accountable, and change your community. Anonymous submissions accepted.
      </p>
      <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
        <a href="<?= url('report') ?>" class="btn-hero-primary">
          <i class="fas fa-flag"></i> Submit a Report
        </a>
        <a href="<?= url('auth/register') ?>" class="btn-hero-outline">
          <i class="fas fa-user-plus"></i> Join NetaTrack
        </a>
      </div>
    </div>
  </div>
</section>

<script>
// Map tooltip
document.querySelectorAll('#indiaMapSvg path, #indiaMapSvg circle').forEach(el=>{
  const tooltip = document.getElementById('mapTooltip');
  el.addEventListener('mousemove', e=>{
    tooltip.style.display='block';
    tooltip.style.left=(e.offsetX+12)+'px';
    tooltip.style.top=(e.offsetY-10)+'px';
    tooltip.textContent = el.dataset.state || '';
  });
  el.addEventListener('mouseleave',()=>tooltip.style.display='none');
  el.addEventListener('click',()=>{
    const state = el.dataset.state;
    if(state) location.href='<?= url('leaders') ?>?state='+encodeURIComponent(state);
  });
});

// Counter animation
const counters = document.querySelectorAll('[data-counter]');
const observer = new IntersectionObserver(entries=>{
  entries.forEach(entry=>{
    if(!entry.isIntersecting) return;
    const el = entry.target;
    const target = parseInt(el.dataset.counter);
    const isPercent = el.textContent.includes('%');
    let cur = 0;
    const step = Math.ceil(target/60);
    const timer = setInterval(()=>{
      cur = Math.min(cur+step, target);
      el.textContent = cur + (isPercent?'%':'');
      if(cur>=target) clearInterval(timer);
    },20);
    observer.unobserve(el);
  });
},{threshold:.3});
counters.forEach(c=>observer.observe(c));
</script>
