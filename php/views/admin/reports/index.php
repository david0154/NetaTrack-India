<?php
// Variables: $reports (paginated), $stats
$page_title = 'Public Reports — NetaTrack Admin';
?>
<div class="page-header">
  <div>
    <div class="page-title">Public Reports
      <span>Review and moderate user-submitted reports</span>
    </div>
  </div>
  <div style="display:flex;gap:.5rem">
    <span class="badge badge-danger" style="padding:.4rem .9rem;font-size:.8rem"><?= $stats['pending']??0 ?> Pending</span>
    <span class="badge badge-success" style="padding:.4rem .9rem;font-size:.8rem"><?= $stats['approved']??0 ?> Approved</span>
  </div>
</div>

<!-- Filters -->
<div class="card" style="margin-bottom:1.25rem">
  <form method="GET" style="display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end">
    <div style="flex:1;min-width:180px">
      <label class="form-label">Search</label>
      <input type="text" name="q" value="<?= e($_GET['q']??'') ?>" class="form-control" placeholder="Title, leader name...">
    </div>
    <div>
      <label class="form-label">Status</label>
      <select name="status" class="form-control">
        <option value="">All</option>
        <option value="pending" <?= ($_GET['status']??'')==='pending'?'selected':'' ?>>Pending</option>
        <option value="approved" <?= ($_GET['status']??'')==='approved'?'selected':'' ?>>Approved</option>
        <option value="rejected" <?= ($_GET['status']??'')==='rejected'?'selected':'' ?>>Rejected</option>
      </select>
    </div>
    <div>
      <label class="form-label">Type</label>
      <select name="type" class="form-control">
        <option value="">All Types</option>
        <option value="corruption">Corruption</option>
        <option value="fake_claim">Fake Claim</option>
        <option value="project_delay">Project Delay</option>
        <option value="promise_broken">Promise Broken</option>
        <option value="positive">Positive</option>
        <option value="other">Other</option>
      </select>
    </div>
    <div style="display:flex;gap:.5rem">
      <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
      <a href="?" class="btn btn-ghost">Reset</a>
    </div>
  </form>
</div>

<!-- Reports Table -->
<div class="card">
  <div class="table-wrapper">
    <table class="admin-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Title / Leader</th>
          <th>Type</th>
          <th>Submitted By</th>
          <th>AI Confidence</th>
          <th>Date</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if(empty($reports['data'])): ?>
        <tr><td colspan="8" style="text-align:center;padding:3rem;color:var(--text-muted)">No reports found.</td></tr>
        <?php else: ?>
        <?php foreach($reports['data'] as $r): ?>
        <tr>
          <td style="color:var(--text-muted);font-size:.8rem"><?= $r['id'] ?></td>
          <td>
            <div style="font-weight:600;font-size:.875rem;color:var(--text-primary)"><?= e(truncate($r['title'],45)) ?></div>
            <?php if(!empty($r['leader_name'])): ?>
              <div style="font-size:.72rem;color:var(--text-muted)"><i class="fas fa-user-tie"></i> <?= e($r['leader_name']) ?></div>
            <?php endif; ?>
          </td>
          <td>
            <?php
              $typeColors=['corruption'=>'danger','fake_claim'=>'warning','project_delay'=>'orange',
                           'promise_broken'=>'purple','positive'=>'success','other'=>'muted'];
            ?>
            <span class="badge badge-<?= $typeColors[$r['type']]??'muted' ?>"><?= e(str_replace('_',' ',$r['type'])) ?></span>
          </td>
          <td>
            <div style="font-size:.85rem"><?= e($r['reporter_name']??'Anonymous') ?></div>
            <div style="font-size:.72rem;color:var(--text-muted)"><?= e($r['reporter_ip']??'') ?></div>
          </td>
          <td>
            <?php $conf = $r['ai_confidence_score']??0; ?>
            <div style="display:flex;align-items:center;gap:.5rem">
              <div style="flex:1;height:5px;background:rgba(255,255,255,0.07);border-radius:3px;min-width:50px">
                <div style="height:100%;width:<?= $conf ?>%;background:<?= $conf>=70?'#22c55e':($conf>=40?'#f59e0b':'#ef4444') ?>;border-radius:3px"></div>
              </div>
              <span style="font-size:.8rem;color:var(--text-muted)"><?= $conf ?>%</span>
            </div>
          </td>
          <td style="font-size:.8rem;color:var(--text-muted)"><?= date('d M Y', strtotime($r['created_at'])) ?></td>
          <td>
            <?php $sc=['pending'=>'warning','approved'=>'success','rejected'=>'danger']; ?>
            <span class="badge badge-<?= $sc[$r['status']]??'muted' ?>"><?= e($r['status']) ?></span>
          </td>
          <td>
            <div style="display:flex;gap:.4rem">
              <?php if($r['status']==='pending'): ?>
              <form method="POST" action="<?= url('admin/reports/'.$r['id'].'/approve') ?>" style="display:inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-success btn-sm btn-icon" title="Approve"
                  data-confirm="Approve this report?">
                  <i class="fas fa-check"></i>
                </button>
              </form>
              <form method="POST" action="<?= url('admin/reports/'.$r['id'].'/reject') ?>" style="display:inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Reject"
                  data-confirm="Reject this report?">
                  <i class="fas fa-times"></i>
                </button>
              </form>
              <?php else: ?>
                <span style="font-size:.75rem;color:var(--text-muted)"><?= e($r['reviewed_by_name']??'—') ?></span>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <?php if(($reports['last_page']??1) > 1): ?>
  <div class="pagination">
    <?php for($p=1;$p<=$reports['last_page'];$p++): ?>
      <a href="?<?= http_build_query(array_merge($_GET,['page'=>$p])) ?>" class="page-link <?= $p==$reports['current_page']?'active':'' ?>"><?= $p ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>
