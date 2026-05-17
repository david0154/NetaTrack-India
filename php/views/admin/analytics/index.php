<?php $page_title = 'Analytics — NetaTrack Admin'; ?>
<div class="page-header">
  <div class="page-title">Analytics
    <span>Platform insights and statistics</span>
  </div>
</div>

<div class="stats-grid" style="margin-bottom:1.5rem">
  <div class="stat-card" style="--stat-color:linear-gradient(90deg,#3b82f6,#8b5cf6);--stat-bg:rgba(59,130,246,0.1)">
    <div class="stat-icon"><i class="fas fa-eye" style="color:#3b82f6"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= number_format($analytics['total_views']??0) ?></div>
      <div class="stat-label">Total Page Views</div>
    </div>
  </div>
  <div class="stat-card" style="--stat-color:linear-gradient(90deg,#22c55e,#16a34a);--stat-bg:rgba(34,197,94,0.1)">
    <div class="stat-icon"><i class="fas fa-users" style="color:#22c55e"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= number_format($analytics['unique_visitors']??0) ?></div>
      <div class="stat-label">Unique Visitors</div>
    </div>
  </div>
  <div class="stat-card" style="--stat-color:linear-gradient(90deg,#f59e0b,#d97706);--stat-bg:rgba(245,158,11,0.1)">
    <div class="stat-icon"><i class="fas fa-flag" style="color:#f59e0b"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= number_format($analytics['reports_this_month']??0) ?></div>
      <div class="stat-label">Reports This Month</div>
    </div>
  </div>
  <div class="stat-card" style="--stat-color:linear-gradient(90deg,#06b6d4,#0891b2);--stat-bg:rgba(6,182,212,0.1)">
    <div class="stat-icon"><i class="fas fa-user-plus" style="color:#06b6d4"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= number_format($analytics['new_users_month']??0) ?></div>
      <div class="stat-label">New Users (30d)</div>
    </div>
  </div>
</div>

<div class="grid-2" style="gap:1.25rem">
  <div class="card">
    <div class="card-header"><div class="card-title"><i class="fas fa-chart-line" style="color:#3b82f6"></i> User Signups (Last 30 Days)</div></div>
    <canvas id="signupChart" height="120"></canvas>
  </div>
  <div class="card">
    <div class="card-header"><div class="card-title"><i class="fas fa-chart-pie" style="color:#8b5cf6"></i> Report Types</div></div>
    <canvas id="reportTypeChart" height="120"></canvas>
  </div>
</div>

<div class="card" style="margin-top:1.25rem">
  <div class="card-header"><div class="card-title"><i class="fas fa-map-marker-alt" style="color:#22c55e"></i> Top States by Activity</div></div>
  <div class="table-wrapper">
    <table class="admin-table">
      <thead><tr><th>State</th><th>Leaders</th><th>Projects</th><th>Reports</th><th>Activity Score</th></tr></thead>
      <tbody>
        <?php foreach($topStates??[] as $state): ?>
        <tr>
          <td style="font-weight:600;color:var(--text-primary)"><?= e($state['name']) ?></td>
          <td><?= $state['leaders_count']??0 ?></td>
          <td><?= $state['projects_count']??0 ?></td>
          <td><?= $state['reports_count']??0 ?></td>
          <td>
            <div style="display:flex;align-items:center;gap:.5rem">
              <div style="flex:1;height:5px;background:rgba(255,255,255,0.07);border-radius:3px;min-width:80px">
                <div style="height:100%;width:<?= min(100,$state['activity_score']??0) ?>%;background:linear-gradient(90deg,#3b82f6,#22c55e);border-radius:3px"></div>
              </div>
              <span style="font-size:.8rem;color:var(--text-muted)"><?= $state['activity_score']??0 ?></span>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  // Signup chart (line)
  new Chart(document.getElementById('signupChart'), {
    type: 'line',
    data: {
      labels: <?= json_encode(array_column($analytics['signup_chart']??[], 'date')) ?>,
      datasets: [{
        label: 'Signups',
        data: <?= json_encode(array_column($analytics['signup_chart']??[], 'count')) ?>,
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59,130,246,.15)',
        fill: true,
        tension: .4,
        pointRadius: 3,
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: {
        x: { ticks: { color:'#94a3b8', maxTicksLimit: 8 }, grid: { color:'rgba(255,255,255,.05)' } },
        y: { ticks: { color:'#94a3b8', stepSize:1 }, grid: { color:'rgba(255,255,255,.05)' }, beginAtZero:true }
      }
    }
  });
  // Report type doughnut
  new Chart(document.getElementById('reportTypeChart'), {
    type: 'doughnut',
    data: {
      labels: <?= json_encode(array_column($analytics['report_types']??[], 'type')) ?>,
      datasets: [{
        data: <?= json_encode(array_column($analytics['report_types']??[], 'count')) ?>,
        backgroundColor: ['#ef4444','#f59e0b','#f97316','#8b5cf6','#22c55e','#94a3b8'],
        borderWidth: 0,
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { position:'bottom', labels:{ color:'#94a3b8', padding:12, font:{size:11} } } },
      cutout: '60%',
    }
  });
});
</script>
