<?php $page_title = 'Projects — NetaTrack Admin'; ?>
<div class="page-header">
  <div class="page-title">Projects
    <span>Track government projects and infrastructure delivery</span>
  </div>
  <a href="<?= url('admin/projects/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Add Project</a>
</div>

<!-- Mini Stats -->
<div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:1.25rem">
  <?php
    $pCards = [
      ['Total Projects', $projectStats['total']??0, '#3b82f6','fas fa-project-diagram'],
      ['Completed',      $projectStats['completed']??0, '#22c55e','fas fa-check-circle'],
      ['In Progress',    $projectStats['in_progress']??0, '#f59e0b','fas fa-spinner'],
      ['Delayed',        $projectStats['delayed']??0, '#ef4444','fas fa-exclamation-circle'],
    ];
    foreach($pCards as [$label,$val,$color,$icon]):
  ?>
  <div class="stat-card" style="--stat-color:<?= $color ?>;--stat-bg:<?= $color ?>1a">
    <div class="stat-icon"><i class="<?= $icon ?>" style="color:<?= $color ?>"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= number_format($val) ?></div>
      <div class="stat-label"><?= $label ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<div class="card">
  <!-- Filters -->
  <form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end;margin-bottom:1.25rem">
    <div style="flex:1;min-width:200px">
      <input type="text" name="q" value="<?= e($_GET['q']??'') ?>" class="form-control" placeholder="🔍 Search projects...">
    </div>
    <select name="status" class="form-control" style="max-width:180px">
      <option value="">All Statuses</option>
      <?php foreach(['planned','in_progress','completed','delayed','cancelled'] as $s): ?>
        <option value="<?= $s ?>" <?= ($_GET['status']??'')===$s?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$s)) ?></option>
      <?php endforeach; ?>
    </select>
    <select name="state_id" class="form-control" style="max-width:160px">
      <option value="">All States</option>
      <?php foreach($states??[] as $st): ?>
        <option value="<?= $st['id'] ?>" <?= ($_GET['state_id']??'')==$st['id']?'selected':'' ?>><?= e($st['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
    <a href="?" class="btn btn-ghost btn-sm">Reset</a>
  </form>

  <div class="table-wrapper">
    <table class="admin-table">
      <thead>
        <tr><th>#</th><th>Project</th><th>Leader</th><th>State</th><th>Budget (₹ Cr)</th><th>Progress</th><th>Deadline</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php if(empty($projects['data'])): ?>
        <tr><td colspan="9" style="text-align:center;padding:3rem;color:var(--text-muted)">No projects found.</td></tr>
        <?php else: ?>
        <?php foreach($projects['data'] as $proj): ?>
        <tr>
          <td style="color:var(--text-muted);font-size:.8rem"><?= $proj['id'] ?></td>
          <td>
            <div style="font-weight:600;font-size:.875rem;color:var(--text-primary)"><?= e(truncate($proj['title'],40)) ?></div>
            <div style="font-size:.72rem;color:var(--text-muted)"><?= e($proj['category']??'—') ?></div>
          </td>
          <td style="font-size:.85rem"><?= e($proj['leader_name']??'—') ?></td>
          <td style="font-size:.82rem;color:var(--text-secondary)"><?= e($proj['state_name']??'National') ?></td>
          <td style="font-size:.85rem;font-weight:600"><?= number_format($proj['budget_crore']??0,0) ?></td>
          <td style="min-width:100px">
            <div style="display:flex;align-items:center;gap:.5rem">
              <div style="flex:1;height:6px;background:rgba(255,255,255,0.07);border-radius:3px">
                <div style="height:100%;width:<?= $proj['completion_pct']??0 ?>%;background:linear-gradient(90deg,#3b82f6,#22c55e);border-radius:3px"></div>
              </div>
              <span style="font-size:.78rem;color:var(--text-muted)"><?= $proj['completion_pct']??0 ?>%</span>
            </div>
          </td>
          <td style="font-size:.8rem;color:var(--text-muted)"><?= $proj['deadline']?date('M Y',strtotime($proj['deadline'])):'—' ?></td>
          <td>
            <?php $sc=['completed'=>'success','in_progress'=>'info','planned'=>'muted','delayed'=>'danger','cancelled'=>'warning']; ?>
            <span class="badge badge-<?= $sc[$proj['status']]??'muted' ?>"><?= e(str_replace('_',' ',$proj['status'])) ?></span>
          </td>
          <td>
            <div style="display:flex;gap:.4rem">
              <a href="<?= url('admin/projects/'.$proj['id'].'/edit') ?>" class="btn btn-ghost btn-sm btn-icon"><i class="fas fa-edit"></i></a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php if(($projects['last_page']??1) > 1): ?>
  <div class="pagination">
    <?php for($p=1;$p<=$projects['last_page'];$p++): ?>
      <a href="?<?= http_build_query(array_merge($_GET,['page'=>$p])) ?>" class="page-link <?= $p==$projects['current_page']?'active':'' ?>"><?= $p ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>
