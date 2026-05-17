<?php
// Variables: $leaderStats, $promiseStats, $projectStats, $reportStats, $userStats,
//            $pendingReports, $topLeaders, $delayedProjects
$page_title = 'Dashboard — NetaTrack Admin';
?>
<div class="page-header">
  <div>
    <div class="page-title">Dashboard
      <span>Welcome back, <?= e(explode(' ',auth()->user()['name']??'Admin')[0]) ?>. Here’s what’s happening.</span>
    </div>
  </div>
  <div style="display:flex;gap:.5rem">
    <a href="<?= url('admin/leaders/create') ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Leader</a>
    <a href="<?= url('admin/reports') ?>" class="btn btn-ghost btn-sm"><i class="fas fa-flag"></i> Reports</a>
  </div>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
  <div class="stat-card" style="--stat-color:linear-gradient(90deg,#3b82f6,#8b5cf6);--stat-bg:rgba(59,130,246,0.1)">
    <div class="stat-icon"><i class="fas fa-user-tie" style="color:#3b82f6"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= number_format($leaderStats['total']) ?></div>
      <div class="stat-label">Leaders Tracked</div>
      <div class="stat-change up"><i class="fas fa-arrow-up"></i> <?= $leaderStats['active'] ?> active</div>
    </div>
  </div>
  <div class="stat-card" style="--stat-color:linear-gradient(90deg,#22c55e,#16a34a);--stat-bg:rgba(34,197,94,0.1)">
    <div class="stat-icon"><i class="fas fa-handshake" style="color:#22c55e"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= number_format($promiseStats['total']) ?></div>
      <div class="stat-label">Promises Tracked</div>
      <div class="stat-change up"><i class="fas fa-check"></i> <?= $promiseStats['completion_rate'] ?>% kept</div>
    </div>
  </div>
  <div class="stat-card" style="--stat-color:linear-gradient(90deg,#f59e0b,#d97706);--stat-bg:rgba(245,158,11,0.1)">
    <div class="stat-icon"><i class="fas fa-project-diagram" style="color:#f59e0b"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= number_format($projectStats['total']) ?></div>
      <div class="stat-label">Projects Monitored</div>
      <div class="stat-change down"><i class="fas fa-exclamation"></i> <?= $projectStats['delayed'] ?> delayed</div>
    </div>
  </div>
  <div class="stat-card" style="--stat-color:linear-gradient(90deg,#ef4444,#dc2626);--stat-bg:rgba(239,68,68,0.1)">
    <div class="stat-icon"><i class="fas fa-flag" style="color:#ef4444"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= number_format($reportStats['total']) ?></div>
      <div class="stat-label">Public Reports</div>
      <div class="stat-change down"><i class="fas fa-clock"></i> <?= $reportStats['pending'] ?> pending review</div>
    </div>
  </div>
  <div class="stat-card" style="--stat-color:linear-gradient(90deg,#06b6d4,#0891b2);--stat-bg:rgba(6,182,212,0.1)">
    <div class="stat-icon"><i class="fas fa-users" style="color:#06b6d4"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= number_format($userStats['total']) ?></div>
      <div class="stat-label">Registered Users</div>
      <div class="stat-change up"><i class="fas fa-check"></i> <?= $userStats['active'] ?> active</div>
    </div>
  </div>
  <div class="stat-card" style="--stat-color:linear-gradient(90deg,#8b5cf6,#7c3aed);--stat-bg:rgba(139,92,246,0.1)">
    <div class="stat-icon"><i class="fas fa-exclamation-triangle" style="color:#8b5cf6"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= number_format($reportStats['fake_detected']??0) ?></div>
      <div class="stat-label">Fake Claims Detected</div>
      <div class="stat-change"><i class="fas fa-shield-alt"></i> AI verified</div>
    </div>
  </div>
</div>

<div class="grid-2" style="gap:1.25rem">

  <!-- Top Leaders -->
  <div class="card">
    <div class="card-header">
      <div class="card-title"><i class="fas fa-trophy" style="color:#f59e0b"></i> Top Leaders</div>
      <a href="<?= url('admin/leaders') ?>" class="btn btn-ghost btn-sm">View All</a>
    </div>
    <?php if(empty($topLeaders)): ?>
      <p style="color:var(--text-muted);text-align:center;padding:2rem 0">No leaders added yet.</p>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:.6rem">
      <?php foreach($topLeaders as $i => $leader): ?>
      <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem;border-radius:8px;background:var(--bg-glass);">
        <div style="width:24px;text-align:center;font-size:.75rem;font-weight:700;color:var(--text-muted)">#<?= $i+1 ?></div>
        <div class="leader-avatar" style="width:36px;height:36px;font-size:.8rem">
          <?php if($leader['photo']): ?>
            <img src="<?= e($leader['photo']) ?>" style="width:100%;height:100%;border-radius:50%;object-fit:cover">
          <?php else: ?>
            <?= strtoupper(substr($leader['name'],0,1)) ?>
          <?php endif; ?>
        </div>
        <div style="flex:1;min-width:0">
          <div style="font-size:.85rem;font-weight:600;color:var(--text-primary);overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($leader['name']) ?></div>
          <div style="font-size:.72rem;color:var(--text-muted)"><?= e($leader['party_name']??'—') ?></div>
        </div>
        <div style="text-align:right">
          <div style="font-size:1rem;font-weight:800;color:var(--color-primary)"><?= $leader['total_score'] ?></div>
          <div class="badge badge-<?= scoreColor($leader['total_score']) === 'excellent' ? 'success' : (scoreColor($leader['total_score']) === 'good' ? 'info' : (scoreColor($leader['total_score']) === 'average' ? 'warning' : 'danger')) ?>" style="font-size:.6rem"><?= e($leader['score_rank']) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <!-- Pending Reports -->
  <div class="card">
    <div class="card-header">
      <div class="card-title"><i class="fas fa-inbox" style="color:#ef4444"></i> Pending Reports
        <?php if($reportStats['pending']??0): ?>
          <span class="badge badge-danger" style="margin-left:.5rem"><?= $reportStats['pending'] ?></span>
        <?php endif; ?>
      </div>
      <a href="<?= url('admin/reports') ?>" class="btn btn-ghost btn-sm">View All</a>
    </div>
    <?php if(empty($pendingReports)): ?>
      <p style="color:var(--text-muted);text-align:center;padding:2rem 0"><i class="fas fa-check-circle" style="color:#22c55e"></i> All clear! No pending reports.</p>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:.5rem">
      <?php foreach($pendingReports as $report): ?>
      <div style="display:flex;align-items:center;gap:.75rem;padding:.6rem .75rem;border-radius:8px;background:var(--bg-glass);">
        <div>
          <span class="badge badge-<?= $report['type']==='corruption'?'danger':($report['type']==='fake_claim'?'warning':'info') ?>">
            <?= e(str_replace('_',' ',$report['type'])) ?>
          </span>
        </div>
        <div style="flex:1;min-width:0">
          <div style="font-size:.82rem;font-weight:600;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--text-primary)"><?= e($report['title']) ?></div>
          <div style="font-size:.7rem;color:var(--text-muted)"><?= timeAgo($report['created_at']) ?></div>
        </div>
        <a href="<?= url('admin/reports') ?>" class="btn btn-ghost btn-sm btn-icon"><i class="fas fa-eye"></i></a>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Score Distribution Chart -->
<div class="card" style="margin-top:1.25rem">
  <div class="card-header">
    <div class="card-title"><i class="fas fa-chart-bar" style="color:#3b82f6"></i> Leader Score Distribution</div>
  </div>
  <canvas id="scoreChart" height="80"></canvas>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const ctx = document.getElementById('scoreChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Excellent (90-100)', 'Good (75-89)', 'Average (50-74)', 'Poor (<50)'],
      datasets: [{
        label: 'Leaders',
        data: [
          <?= $leaderStats['excellent']??0 ?>,
          <?= $leaderStats['good']??0 ?>,
          <?= $leaderStats['average']??0 ?>,
          <?= $leaderStats['poor']??0 ?>
        ],
        backgroundColor: ['rgba(34,197,94,.7)','rgba(6,182,212,.7)','rgba(245,158,11,.7)','rgba(239,68,68,.7)'],
        borderColor:     ['#22c55e','#06b6d4','#f59e0b','#ef4444'],
        borderWidth: 2,
        borderRadius: 6,
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false }
      },
      scales: {
        x: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(255,255,255,0.05)' } },
        y: { ticks: { color: '#94a3b8', stepSize: 1 }, grid: { color: 'rgba(255,255,255,0.05)' }, beginAtZero: true }
      }
    }
  });
});
</script>
