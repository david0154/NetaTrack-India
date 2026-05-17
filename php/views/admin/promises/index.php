<div class="grid grid-2">
    <section class="panel form-panel">
        <div class="panel__head"><h2>Add Promise</h2></div>
        <form method="post" action="/admin/promises/create" class="form-grid">
            <input type="hidden" name="_token" value="<?= $this->csrf() ?>">
            <label><span>Leader</span><select name="leader_id" required><?php foreach($leaders as $leader): ?><option value="<?= (int)$leader['id'] ?>"><?= htmlspecialchars($leader['name']) ?></option><?php endforeach; ?></select></label>
            <label><span>Category</span><input name="category"></label>
            <label class="col-2"><span>Title</span><input name="title" required></label>
            <label class="col-2"><span>Description</span><textarea name="description" rows="4"></textarea></label>
            <label><span>Made On</span><input type="date" name="made_on"></label>
            <label><span>Deadline</span><input type="date" name="deadline"></label>
            <label><span>Status</span><select name="status"><option>pending</option><option>in_progress</option><option>completed</option><option>broken</option><option>fake</option></select></label>
            <label><span>Source URL</span><input name="source_url"></label>
            <label><span>Proof URL</span><input name="proof_url"></label>
            <div class="col-2"><button class="btn btn--primary" type="submit">Save Promise</button></div>
        </form>
    </section>

    <section class="panel">
        <div class="panel__head"><h2>Promises List</h2></div>
        <table class="table">
            <thead><tr><th>Title</th><th>Leader</th><th>Status</th><th>Deadline</th></tr></thead>
            <tbody>
            <?php foreach($promises as $promise): ?>
                <tr>
                    <td><?= htmlspecialchars($promise['title']) ?></td>
                    <td><?= htmlspecialchars($promise['leader_name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($promise['status']) ?></td>
                    <td><?= htmlspecialchars((string)$promise['deadline']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>
