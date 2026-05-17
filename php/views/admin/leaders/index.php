<div class="toolbar">
    <a class="btn btn--primary" href="/admin/leaders/create">Add Leader</a>
</div>
<section class="panel">
    <div class="panel__head"><h2>All Leaders</h2></div>
    <table class="table">
        <thead><tr><th>Name</th><th>Party</th><th>State</th><th>Score</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($leaders as $leader): ?>
            <tr>
                <td><?= htmlspecialchars($leader['name']) ?></td>
                <td><?= htmlspecialchars($leader['party_name'] ?? '-') ?></td>
                <td><?= htmlspecialchars($leader['state_name'] ?? '-') ?></td>
                <td><?= htmlspecialchars((string)$leader['overall_score']) ?></td>
                <td><?= htmlspecialchars($leader['status']) ?></td>
                <td class="actions">
                    <a class="btn btn--ghost" href="/admin/leaders/<?= (int)$leader['id'] ?>/edit">Edit</a>
                    <form method="post" action="/admin/leaders/<?= (int)$leader['id'] ?>/delete" onsubmit="return confirm('Delete this leader?')">
                        <input type="hidden" name="_token" value="<?= $this->csrf() ?>">
                        <button class="btn btn--danger" type="submit">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
