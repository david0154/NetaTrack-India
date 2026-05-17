<?php
// Variables: $leaders (paginated), $states, $parties
$page_title = 'Leaders — NetaTrack Admin';
?>
<div class="page-header">
  <div>
    <div class="page-title">Leaders
      <span>Manage political leaders across India</span>
    </div>
  </div>
  <a href="<?= url('admin/leaders/create') ?>" class="btn btn-primary">
    <i class="fas fa-plus"></i> Add Leader
  </a>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:1.25rem">
  <form method="GET" action="<?= url('admin/leaders') ?>" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end">
    <div style="flex:1;min-width:200px">
      <label class="form-label">Search</label>
      <input type="text" name="q" value="<?= e($_GET['q']??'') ?>" placeholder="Name, constituency..." class="form-control">
    </div>
    <div>
      <label class="form-label">State</label>
      <select name="state" class="form-control">
        <option value="">All States</option>
        <?php foreach($states??[] as $s): ?>
          <option value="<?= $s['id'] ?>" <?= ($_GET['state']??'')==$s['id']?'selected':'' ?>><?= e($s['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="form-label">Party</label>
      <select name="party" class="form-control">
        <option value="">All Parties</option>
        <?php foreach($parties??[] as $p): ?>
          <option value="<?= $p['id'] ?>" <?= ($_GET['party']??'')==$p['id']?'selected':'' ?>><?= e($p['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label class="form-label">Rank</label>
      <select name="rank" class="form-control">
        <option value="">All Ranks</option>
        <option value="Excellent">Excellent</option>
        <option value="Good">Good</option>
        <option value="Average">Average</option>
        <option value="Poor">Poor</option>
      </select>
    </div>
    <div style="display:flex;gap:.5rem">
      <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
      <a href="<?= url('admin/leaders') ?>" class="btn btn-ghost">Reset</a>
    </div>
  </form>
</div>

<!-- Table -->
<div class="card">
  <div class="table-wrapper">
    <table class="admin-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Leader</th>
          <th>Party</th>
          <th>State</th>
          <th>Designation</th>
          <th>Score</th>
          <th>Rank</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if(empty($leaders['data'])): ?>
        <tr><td colspan="9" style="text-align:center;padding:3rem;color:var(--text-muted)">No leaders found. <a href="<?= url('admin/leaders/create') ?>">Add the first one</a>.</td></tr>
        <?php else: ?>
        <?php foreach($leaders['data'] as $leader): ?>
        <tr>
          <td style="color:var(--text-muted);font-size:.8rem"><?= $leader['id'] ?></td>
          <td>
            <div class="leader-cell">
              <div class="leader-avatar"><?= strtoupper(substr($leader['name'],0,1)) ?></div>
              <div>
                <div class="leader-name"><?= e($leader['name']) ?></div>
                <div class="leader-sub"><?= e($leader['constituency']??'—') ?></div>
              </div>
            </div>
          </td>
          <td>
            <span style="display:inline-flex;align-items:center;gap:.35rem">
              <span style="width:8px;height:8px;border-radius:50%;background:<?= e($leader['party_color']??'#3b82f6') ?>;display:inline-block"></span>
              <?= e($leader['party_name']??'—') ?>
            </span>
          </td>
          <td><?= e($leader['state_name']??'—') ?></td>
          <td style="font-size:.8rem"><?= e(truncate($leader['designation']??'—',30)) ?></td>
          <td>
            <div style="display:flex;align-items:center;gap:.5rem">
              <div style="flex:1;height:6px;background:rgba(255,255,255,0.07);border-radius:3px;min-width:60px">
                <div style="height:100%;width:<?= $leader['total_score'] ?>%;background:linear-gradient(90deg,#3b82f6,#8b5cf6);border-radius:3px"></div>
              </div>
              <span style="font-weight:700;font-size:.85rem;color:var(--text-primary)"><?= $leader['total_score'] ?></span>
            </div>
          </td>
          <td>
            <?php $rankColors=['Excellent'=>'success','Good'=>'info','Average'=>'warning','Poor'=>'danger']; ?>
            <span class="badge badge-<?= $rankColors[$leader['score_rank']]??'muted' ?>"><?= e($leader['score_rank']) ?></span>
          </td>
          <td>
            <span class="badge badge-<?= $leader['status']==='active'?'success':'muted' ?>"><?= e($leader['status']) ?></span>
          </td>
          <td>
            <div style="display:flex;gap:.4rem">
              <a href="<?= url('admin/leaders/'.$leader['id'].'/edit') ?>" class="btn btn-ghost btn-sm btn-icon" title="Edit">
                <i class="fas fa-edit"></i>
              </a>
              <form method="POST" action="<?= url('admin/leaders/'.$leader['id'].'/delete') ?>" style="display:inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Delete"
                  data-confirm="Delete <?= e($leader['name']) ?>? This cannot be undone.">
                  <i class="fas fa-trash"></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if(($leaders['last_page']??1) > 1): ?>
  <div class="pagination">
    <?php for($p=1; $p<=$leaders['last_page']; $p++): ?>
      <a href="?<?= http_build_query(array_merge($_GET,['page'=>$p])) ?>" class="page-link <?= $p==$leaders['current_page']?'active':'' ?>"><?= $p ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>
