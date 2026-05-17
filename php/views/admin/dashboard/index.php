<?php $page_title = 'Dashboard'; ?>
<div class="dashboard">

    <!-- Stats grid -->
    <div class="stats-grid">
        <?php
        $cards = [
            ['Leaders',          $stats['leaders'],  '👤', '#f97316'],
            ['Promises',         $stats['promises'], '📜', '#3b82f6'],
            ['Projects',         $stats['projects'], '🏗️', '#22c55e'],
            ['Reports',          $stats['reports'],  '📋', '#8b5cf6'],
            ['Users',            $stats['users'],    '👥', '#06b6d4'],
            ['Pending Reviews',  $stats['pending'],  '⏳', '#ef4444'],
        ];
        foreach ($cards as [$label, $value, $icon, $color]): ?>
        <div class="stat-card" style="border-left: 4px solid <?= $color ?>">
            <div class="stat-icon"><?= $icon ?></div>
            <div class="stat-value"><?= number_format($value) ?></div>
            <div class="stat-label"><?= $label ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="dashboard-grid">

        <!-- Top Leaders -->
        <section class="dash-card">
            <h3 class="dash-card-title">🏆 Top Rated Leaders</h3>
            <table class="admin-table">
                <thead><tr><th>Leader</th><th>Party</th><th>Score</th><th>Rank</th></tr></thead>
                <tbody>
                <?php foreach ($topLeaders as $l): ?>
                <tr>
                    <td><a href="<?= url('admin/leaders/'.$l['id'].'/edit') ?>"><?= e($l['name']) ?></a></td>
                    <td><span class="party-badge" style="background:<?= e($l['party_color']) ?>22;color:<?= e($l['party_color']) ?>"><?= e($l['party_abbr']) ?></span></td>
                    <td><span class="score-pill" style="background:<?= scoreColor($l['total_score']) ?>22;color:<?= scoreColor($l['total_score']) ?>"><?= $l['total_score'] ?></span></td>
                    <td><?= e($l['score_rank']) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <!-- Pending Reports -->
        <section class="dash-card">
            <h3 class="dash-card-title">⏳ Pending Reports <a href="<?= url('admin/reports') ?>" class="view-all">View All</a></h3>
            <?php if (empty($pendingReports)): ?>
            <p class="empty">No pending reports. 🎉</p>
            <?php else: ?>
            <?php foreach ($pendingReports as $r): ?>
            <div class="report-row">
                <div class="report-meta">
                    <span class="report-type type-<?= $r['type'] ?>"><?= ucfirst(str_replace('_',' ',$r['type'])) ?></span>
                    <span class="report-leader"><?= e($r['leader_name'] ?? 'Unknown') ?></span>
                </div>
                <div class="report-title"><?= e(truncate($r['title'], 70)) ?></div>
                <div class="report-actions">
                    <form method="POST" action="<?= url('admin/reports/'.$r['id'].'/approve') ?>" style="display:inline">
                        <?= csrf_field() ?>
                        <button class="btn-xs btn-success">Approve</button>
                    </form>
                    <form method="POST" action="<?= url('admin/reports/'.$r['id'].'/reject') ?>" style="display:inline">
                        <?= csrf_field() ?>
                        <button class="btn-xs btn-danger">Reject</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </section>

    </div>
</div>
