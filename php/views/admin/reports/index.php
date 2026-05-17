<section class="panel">
    <div class="panel__head"><h2>Public Reports Queue</h2></div>
    <table class="table">
        <thead><tr><th>Title</th><th>User</th><th>Type</th><th>Leader</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach($reports as $report): ?>
            <tr>
                <td><?= htmlspecialchars($report['title']) ?></td>
                <td><?= htmlspecialchars($report['user_name'] ?? 'Anonymous') ?></td>
                <td><?= htmlspecialchars($report['type']) ?></td>
                <td><?= htmlspecialchars($report['leader_name'] ?? '-') ?></td>
                <td><?= htmlspecialchars($report['status']) ?></td>
                <td class="actions">
                    <form method="post" action="/admin/reports/<?= (int)$report['id'] ?>/approve">
                        <input type="hidden" name="_token" value="<?= $this->csrf() ?>">
                        <button class="btn btn--success" type="submit">Approve</button>
                    </form>
                    <form method="post" action="/admin/reports/<?= (int)$report['id'] ?>/reject">
                        <input type="hidden" name="_token" value="<?= $this->csrf() ?>">
                        <input type="hidden" name="admin_notes" value="Rejected by admin review">
                        <button class="btn btn--danger" type="submit">Reject</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
