<?php $page_title = 'Reports'; ?>
<div class="reports-page">

    <!-- Filter bar -->
    <form method="GET" class="filter-bar">
        <input type="text" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="🔍 Search reports...">
        <select name="status">
            <?php foreach ([''=>'All Status','pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $v=>$l): ?>
            <option value="<?= $v ?>" <?= ($_GET['status'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
            <?php endforeach; ?>
        </select>
        <select name="type">
            <?php foreach ([''=>'All Types','corruption'=>'Corruption','fake_claim'=>'Fake Claim','project_delay'=>'Project Delay','promise_broken'=>'Promise Broken','positive'=>'Positive','other'=>'Other'] as $v=>$l): ?>
            <option value="<?= $v ?>" <?= ($_GET['type'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        <a href="<?= url('admin/reports') ?>" class="btn btn-secondary">Reset</a>
    </form>

    <!-- Table -->
    <?php if (empty($reports['data'])): ?>
    <div class="empty"><div class="empty-icon">📋</div><p>No reports found.</p></div>
    <?php else: ?>
    <table class="admin-table">
        <thead>
            <tr>
                <th>#</th><th>Leader</th><th>Title</th><th>Type</th>
                <th>AI Conf</th><th>Status</th><th>Date</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($reports['data'] as $r): ?>
        <tr>
            <td><?= $r['id'] ?></td>
            <td><?= e($r['leader_name'] ?? 'Unknown') ?></td>
            <td title="<?= e($r['title']) ?>"><?= e(truncate($r['title'], 55)) ?></td>
            <td><span class="report-type type-<?= $r['type'] ?>"><?= ucfirst(str_replace('_',' ',$r['type'])) ?></span></td>
            <td>
                <span style="color:<?= $r['ai_confidence'] >= 70 ? '#22c55e' : ($r['ai_confidence'] >= 40 ? '#f59e0b' : '#ef4444') ?>">
                    <?= $r['ai_confidence'] ?>%
                </span>
            </td>
            <td><span class="status-badge status-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
            <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
            <td>
                <?php if ($r['status'] === 'pending'): ?>
                <form method="POST" action="<?= url('admin/reports/'.$r['id'].'/approve') ?>" style="display:inline">
                    <?= csrf_field() ?>
                    <button class="btn-xs btn-success">Approve</button>
                </form>
                <form method="POST" action="<?= url('admin/reports/'.$r['id'].'/reject') ?>" style="display:inline">
                    <?= csrf_field() ?>
                    <textarea name="admin_note" style="display:none"></textarea>
                    <button class="btn-xs btn-danger">Reject</button>
                </form>
                <?php else: ?>
                <span class="status-badge status-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if ($reports['last_page'] > 1): ?>
    <div class="pagination">
        <?php for ($p = 1; $p <= $reports['last_page']; $p++): ?>
        <a href="?page=<?= $p ?>&status=<?= e($_GET['status']??'') ?>&type=<?= e($_GET['type']??'') ?>&q=<?= e($_GET['q']??'') ?>"
           class="page-btn <?= $p == $reports['current_page'] ? 'active' : '' ?>"><?= $p ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
