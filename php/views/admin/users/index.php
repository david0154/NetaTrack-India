<?php $page_title = 'Users'; ?>
<div class="users-page">

    <form method="GET" class="filter-bar">
        <input type="text" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="🔍 Search users...">
        <select name="role">
            <option value="">All Roles</option>
            <?php foreach (['user'=>'User','moderator'=>'Moderator','admin'=>'Admin'] as $v=>$l): ?>
            <option value="<?= $v ?>" <?= ($_GET['role'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        <a href="<?= url('admin/users') ?>" class="btn btn-secondary">Reset</a>
    </form>

    <?php if (empty($users['data'])): ?>
    <div class="empty"><div class="empty-icon">👥</div><p>No users found.</p></div>
    <?php else: ?>
    <table class="admin-table">
        <thead>
            <tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($users['data'] as $u): ?>
        <tr>
            <td><?= $u['id'] ?></td>
            <td><?= e($u['name']) ?></td>
            <td><?= e($u['email']) ?></td>
            <td>
                <span class="status-badge" style="background:<?= $u['role']==='admin' ? 'rgba(249,115,22,.12)' : 'rgba(59,130,246,.12)' ?>;color:<?= $u['role']==='admin' ? '#fb923c' : '#93c5fd' ?>">
                    <?= ucfirst($u['role']) ?>
                </span>
            </td>
            <td><span class="status-badge status-<?= $u['status'] ?>"><?= ucfirst($u['status']) ?></span></td>
            <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
            <td>
                <?php if ($u['role'] !== 'admin'): ?>
                <form method="POST" action="<?= url('admin/users/'.$u['id'].'/ban') ?>" style="display:inline">
                    <?= csrf_field() ?>
                    <button class="btn-xs btn-danger"><?= $u['status']==='banned' ? 'Unban' : 'Ban' ?></button>
                </form>
                <?php else: ?>
                <span style="color:var(--text-3);font-size:.75rem">🛡️ Protected</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($users['last_page'] > 1): ?>
    <div class="pagination">
        <?php for ($p = 1; $p <= $users['last_page']; $p++): ?>
        <a href="?page=<?= $p ?>&role=<?= e($_GET['role']??'') ?>&q=<?= e($_GET['q']??'') ?>"
           class="page-btn <?= $p == $users['current_page'] ? 'active' : '' ?>"><?= $p ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
