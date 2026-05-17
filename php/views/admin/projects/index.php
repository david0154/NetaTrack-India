<?php $page_title = 'Projects'; ?>
<div class="projects-page">

    <!-- Stats -->
    <div class="stats-grid" style="margin-bottom:1.25rem">
        <?php
        $sc = [
            ['Total',       $stats['total'],       '🏗️', '#3b82f6'],
            ['Completed',   $stats['completed']??0,'✅','#22c55e'],
            ['In Progress', $stats['in_progress']??0,'🔄','#06b6d4'],
            ['Delayed',     $stats['delayed']??0,  '⚠️', '#f59e0b'],
            ['Budget',      '&#8377;'.number_format(($stats['total_budget']??0)/1,0).'Cr','💰','#8b5cf6'],
        ];
        foreach ($sc as [$label,$value,$icon,$color]): ?>
        <div class="stat-card" style="border-left:4px solid <?= $color ?>">
            <div class="stat-icon"><?= $icon ?></div>
            <div class="stat-value"><?= $value ?></div>
            <div class="stat-label"><?= $label ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <form method="GET" class="filter-bar">
        <input type="text" name="q" value="<?= e($_GET['q'] ?? '') ?>" placeholder="🔍 Search projects...">
        <select name="status">
            <?php foreach ([''=>'All','planned'=>'Planned','in_progress'=>'In Progress','completed'=>'Completed','delayed'=>'Delayed','cancelled'=>'Cancelled'] as $v=>$l): ?>
            <option value="<?= $v ?>" <?= ($_GET['status'] ?? '') === $v ? 'selected' : '' ?>><?= $l ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
    </form>

    <?php if (empty($projects['data'])): ?>
    <div class="empty"><div class="empty-icon">🏗️</div><p>No projects found.</p></div>
    <?php else: ?>
    <table class="admin-table">
        <thead>
            <tr><th>Title</th><th>Leader</th><th>State</th><th>Status</th><th>Budget</th><th>Progress</th><th>Deadline</th></tr>
        </thead>
        <tbody>
        <?php foreach ($projects['data'] as $pr): ?>
        <tr>
            <td title="<?= e($pr['title']) ?>"><?= e(truncate($pr['title'],55)) ?></td>
            <td><?= e($pr['leader_name'] ?? '—') ?></td>
            <td><?= e($pr['state_name'] ?? '—') ?></td>
            <td>
                <?php
                $sc = match($pr['status']) {
                    'completed'   => ['#22c55e','status-active'],
                    'in_progress' => ['#06b6d4','status-active'],
                    'delayed'     => ['#f59e0b','status-pending'],
                    'cancelled'   => ['#ef4444','status-banned'],
                    default       => ['#94a3b8','status-inactive'],
                };
                ?>
                <span class="status-badge" style="background:<?= $sc[0] ?>22;color:<?= $sc[0] ?>">
                    <?= ucfirst(str_replace('_',' ',$pr['status'])) ?>
                </span>
            </td>
            <td><?= $pr['budget_crore'] ? formatCrore((float)$pr['budget_crore']) : '—' ?></td>
            <td>
                <div style="display:flex;align-items:center;gap:.5rem">
                    <div class="bar-track" style="width:80px"><div class="bar-fill" style="width:<?= $pr['completion_pct'] ?>%"></div></div>
                    <span style="font-size:.75rem;color:var(--text-3)"><?= $pr['completion_pct'] ?>%</span>
                </div>
            </td>
            <td style="font-size:.8rem;color:var(--text-3)"><?= $pr['deadline'] ? date('d M Y', strtotime($pr['deadline'])) : '—' ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($projects['last_page'] > 1): ?>
    <div class="pagination">
        <?php for ($p = 1; $p <= $projects['last_page']; $p++): ?>
        <a href="?page=<?= $p ?>&status=<?= e($_GET['status']??'') ?>&q=<?= e($_GET['q']??'') ?>"
           class="page-btn <?= $p == $projects['current_page'] ? 'active' : '' ?>"><?= $p ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>
