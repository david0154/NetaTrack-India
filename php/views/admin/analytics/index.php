<?php $page_title = 'Analytics'; ?>
<div class="analytics-page">
    <h2 style="margin-bottom:1.25rem;font-size:1.2rem;font-weight:700">📈 Platform Analytics</h2>

    <div class="analytics-grid">

        <!-- Report Types -->
        <div class="chart-card">
            <h3>📋 Report Types Distribution</h3>
            <?php if (empty($reportTypes)): ?>
            <p style="color:var(--text-3);font-size:.875rem">No data yet.</p>
            <?php else:
                $maxR = max(array_column($reportTypes,'cnt'));
                foreach ($reportTypes as $row):
                    $pct = $maxR > 0 ? round(($row['cnt']/$maxR)*100) : 0;
            ?>
            <div class="bar-row">
                <span class="bar-label"><?= ucfirst(str_replace('_',' ',$row['type'])) ?></span>
                <div class="bar-track"><div class="bar-fill" style="width:<?= $pct ?>%"></div></div>
                <span class="bar-val"><?= $row['cnt'] ?></span>
            </div>
            <?php endforeach; endif; ?>
        </div>

        <!-- Top States -->
        <div class="chart-card">
            <h3>🗺️ Top States by Reports</h3>
            <?php if (empty($topStates)): ?>
            <p style="color:var(--text-3);font-size:.875rem">No data yet.</p>
            <?php else:
                $maxS = max(array_column($topStates,'cnt'));
                foreach ($topStates as $row):
                    $pct = $maxS > 0 ? round(($row['cnt']/$maxS)*100) : 0;
            ?>
            <div class="bar-row">
                <span class="bar-label"><?= e($row['name']) ?></span>
                <div class="bar-track"><div class="bar-fill" style="width:<?= $pct ?>%;background:linear-gradient(90deg,#f97316,#f59e0b)"></div></div>
                <span class="bar-val"><?= $row['cnt'] ?></span>
            </div>
            <?php endforeach; endif; ?>
        </div>

        <!-- User Signups (30 days) -->
        <div class="chart-card" style="grid-column:1/-1">
            <h3>👥 User Signups — Last 30 Days</h3>
            <?php if (empty($signupData)): ?>
            <p style="color:var(--text-3);font-size:.875rem">No signups in the last 30 days.</p>
            <?php else:
                $maxU = max(array_column($signupData,'cnt'));
                $totalSigns = array_sum(array_column($signupData,'cnt'));
            ?>
            <p style="color:var(--text-3);font-size:.85rem;margin-bottom:1rem">
                Total: <strong style="color:var(--text-1)"><?= $totalSigns ?></strong> new users
            </p>
            <div style="display:flex;align-items:flex-end;gap:3px;height:80px">
                <?php foreach ($signupData as $row):
                    $h = $maxU > 0 ? max(4, round(($row['cnt']/$maxU)*80)) : 4;
                ?>
                <div title="<?= e($row['d']) ?>: <?= $row['cnt'] ?> signups"
                     style="flex:1;height:<?= $h ?>px;background:linear-gradient(180deg,#3b82f6,#06b6d4);border-radius:3px 3px 0 0;min-width:3px"></div>
                <?php endforeach; ?>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:.7rem;color:var(--text-3);margin-top:.4rem">
                <span><?= e($signupData[0]['d'] ?? '') ?></span>
                <span><?= e(end($signupData)['d'] ?? '') ?></span>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>
