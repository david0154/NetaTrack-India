<div class="grid grid-2">
    <section class="panel form-panel">
        <div class="panel__head"><h2>Add Project</h2></div>
        <form method="post" action="/admin/projects/create" class="form-grid">
            <input type="hidden" name="_token" value="<?= $this->csrf() ?>">
            <label class="col-2"><span>Title</span><input name="title" required></label>
            <label><span>Leader</span><select name="leader_id"><option value="">Select</option><?php foreach($leaders as $leader): ?><option value="<?= (int)$leader['id'] ?>"><?= htmlspecialchars($leader['name']) ?></option><?php endforeach; ?></select></label>
            <label><span>State</span><select name="state_id"><option value="">Select</option><?php foreach($states as $state): ?><option value="<?= (int)$state['id'] ?>"><?= htmlspecialchars($state['name']) ?></option><?php endforeach; ?></select></label>
            <label><span>Category</span><input name="category"></label>
            <label><span>Allocated Budget</span><input type="number" step="0.01" name="allocated_budget"></label>
            <label><span>Spent Budget</span><input type="number" step="0.01" name="spent_budget"></label>
            <label><span>Start Date</span><input type="date" name="start_date"></label>
            <label><span>Expected End Date</span><input type="date" name="expected_end_date"></label>
            <label><span>Progress %</span><input type="number" name="progress_pct" min="0" max="100" value="0"></label>
            <label><span>Status</span><select name="status"><option>planned</option><option>in_progress</option><option>delayed</option><option>completed</option><option>cancelled</option></select></label>
            <label><span>Contractor</span><input name="contractor"></label>
            <label><span>Tender URL</span><input name="tender_url"></label>
            <label><span>Source URL</span><input name="source_url"></label>
            <label class="col-2"><span>Description</span><textarea name="description" rows="4"></textarea></label>
            <div class="col-2"><button class="btn btn--primary" type="submit">Save Project</button></div>
        </form>
    </section>

    <section class="panel">
        <div class="panel__head"><h2>Projects List</h2></div>
        <table class="table">
            <thead><tr><th>Title</th><th>State</th><th>Status</th><th>Progress</th></tr></thead>
            <tbody>
            <?php foreach($projects as $project): ?>
                <tr>
                    <td><?= htmlspecialchars($project['title']) ?></td>
                    <td><?= htmlspecialchars($project['state_name'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($project['status']) ?></td>
                    <td><?= (int)$project['progress_pct'] ?>%</td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>
