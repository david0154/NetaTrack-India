<div class="grid stats-grid">
    <div class="stat-card"><span>Total Leaders</span><strong><?= (int)$stats['leaders'] ?></strong></div>
    <div class="stat-card"><span>Promises Tracked</span><strong><?= (int)$stats['promises'] ?></strong></div>
    <div class="stat-card"><span>Projects</span><strong><?= (int)$stats['projects'] ?></strong></div>
    <div class="stat-card"><span>Pending Reports</span><strong><?= (int)$stats['reports_pending'] ?></strong></div>
    <div class="stat-card"><span>Approved Reports</span><strong><?= (int)$stats['reports_approved'] ?></strong></div>
    <div class="stat-card"><span>Corruption Cases</span><strong><?= (int)$stats['corruption_cases'] ?></strong></div>
    <div class="stat-card"><span>Users</span><strong><?= (int)$stats['users'] ?></strong></div>
    <div class="stat-card"><span>Settings Keys</span><strong><?= (int)$stats['settings'] ?></strong></div>
</div>

<div class="grid grid-2">
    <section class="panel">
        <div class="panel__head"><h2>Traffic Overview</h2></div>
        <div class="mini-stats">
            <div><span>Today</span><strong><?= (int)$traffic['today'] ?></strong></div>
            <div><span>Week</span><strong><?= (int)$traffic['week'] ?></strong></div>
            <div><span>Month</span><strong><?= (int)$traffic['month'] ?></strong></div>
        </div>
    </section>

    <section class="panel">
        <div class="panel__head"><h2>Top Leaders</h2></div>
        <div class="list-stack">
            <?php foreach ($topLeaders as $leader): ?>
                <div class="list-row">
                    <div>
                        <strong><?= htmlspecialchars($leader['name']) ?></strong>
                        <span><?= htmlspecialchars($leader['rank'] ?? 'Average') ?></span>
                    </div>
                    <span class="badge badge--info">Score <?= htmlspecialchars((string)$leader['overall_score']) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<div class="grid grid-2">
    <section class="panel">
        <div class="panel__head"><h2>Recent Public Reports</h2></div>
        <table class="table">
            <thead><tr><th>Title</th><th>User</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($recentReports as $report): ?>
                <tr>
                    <td><?= htmlspecialchars($report['title']) ?></td>
                    <td><?= htmlspecialchars($report['user_name'] ?? 'Anonymous') ?></td>
                    <td><span class="badge badge--warning"><?= htmlspecialchars($report['status']) ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section class="panel">
        <div class="panel__head"><h2>Delayed Projects</h2></div>
        <table class="table">
            <thead><tr><th>Project</th><th>Progress</th><th>Deadline</th></tr></thead>
            <tbody>
            <?php foreach ($delayedProjects as $project): ?>
                <tr>
                    <td><?= htmlspecialchars($project['title']) ?></td>
                    <td><?= (int)$project['progress_pct'] ?>%</td>
                    <td><?= htmlspecialchars((string)$project['expected_end_date']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>

<section class="panel">
    <div class="panel__head"><h2>AI Scraper Queue</h2></div>
    <table class="table">
        <thead><tr><th>Title</th><th>Source Type</th><th>AI Confidence</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($aiQueue as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['title']) ?></td>
                <td><?= htmlspecialchars($item['source_type']) ?></td>
                <td><?= htmlspecialchars((string)$item['ai_confidence']) ?></td>
                <td><?= htmlspecialchars($item['status']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</section>
