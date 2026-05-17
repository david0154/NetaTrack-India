<?php $page_title = 'Users — NetaTrack Admin'; ?>
<div class="page-header">
  <div class="page-title">Users
    <span>Manage registered platform users</span>
  </div>
</div>

<div class="card">
  <!-- Filters -->
  <form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end;margin-bottom:1.25rem">
    <div style="flex:1;min-width:200px">
      <input type="text" name="q" value="<?= e($_GET['q']??'') ?>" class="form-control" placeholder="🔍 Search by name or email...">
    </div>
    <select name="role" class="form-control" style="max-width:160px">
      <option value="">All Roles</option>
      <option value="user" <?= ($_GET['role']??'')==='user'?'selected':'' ?>>User</option>
      <option value="admin" <?= ($_GET['role']??'')==='admin'?'selected':'' ?>>Admin</option>
      <option value="super_admin" <?= ($_GET['role']??'')==='super_admin'?'selected':'' ?>>Super Admin</option>
    </select>
    <select name="status" class="form-control" style="max-width:140px">
      <option value="">All Statuses</option>
      <option value="active" <?= ($_GET['status']??'')==='active'?'selected':'' ?>>Active</option>
      <option value="banned" <?= ($_GET['status']??'')==='banned'?'selected':'' ?>>Banned</option>
    </select>
    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
    <a href="?" class="btn btn-ghost btn-sm">Reset</a>
  </form>

  <div class="table-wrapper">
    <table class="admin-table">
      <thead>
        <tr><th>#</th><th>User</th><th>Email</th><th>Role</th><th>Reports</th><th>Joined</th><th>Status</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php if(empty($users['data'])): ?>
        <tr><td colspan="8" style="text-align:center;padding:3rem;color:var(--text-muted)">No users found.</td></tr>
        <?php else: ?>
        <?php foreach($users['data'] as $u): ?>
        <tr>
          <td style="color:var(--text-muted);font-size:.8rem"><?= $u['id'] ?></td>
          <td>
            <div class="leader-cell">
              <div class="leader-avatar"><?= strtoupper(substr($u['name'],0,1)) ?></div>
              <div class="leader-name"><?= e($u['name']) ?></div>
            </div>
          </td>
          <td style="font-size:.85rem;color:var(--text-secondary)"><?= e($u['email']) ?></td>
          <td>
            <?php $rc=['super_admin'=>'danger','admin'=>'warning','user'=>'muted']; ?>
            <span class="badge badge-<?= $rc[$u['role']]??'muted' ?>"><?= e(str_replace('_',' ',$u['role'])) ?></span>
          </td>
          <td style="font-size:.85rem;font-weight:600;color:var(--text-primary)"><?= $u['reports_count']??0 ?></td>
          <td style="font-size:.8rem;color:var(--text-muted)"><?= date('d M Y',strtotime($u['created_at'])) ?></td>
          <td>
            <span class="badge badge-<?= $u['status']==='active'?'success':'danger' ?>"><?= e($u['status']) ?></span>
          </td>
          <td>
            <?php if($u['status']==='active' && $u['role']==='user'): ?>
            <form method="POST" action="<?= url('admin/users/'.$u['id'].'/ban') ?>">
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-danger btn-sm" data-confirm="Ban user <?= e($u['name']) ?>?">
                <i class="fas fa-ban"></i> Ban
              </button>
            </form>
            <?php else: ?>
              <span style="color:var(--text-muted);font-size:.8rem">—</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php if(($users['last_page']??1) > 1): ?>
  <div class="pagination">
    <?php for($p=1;$p<=$users['last_page'];$p++): ?>
      <a href="?<?= http_build_query(array_merge($_GET,['page'=>$p])) ?>" class="page-link <?= $p==$users['current_page']?'active':'' ?>"><?= $p ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>
