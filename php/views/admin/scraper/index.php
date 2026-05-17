<?php $page_title = 'AI Scraper'; ?>
<div class="scraper-page">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem">
        <div>
            <h2 style="font-size:1.2rem;font-weight:700">🤖 AI News Scraper</h2>
            <p style="color:var(--text-3);font-size:.875rem;margin-top:.25rem">Automatically fetch and analyze political news from Indian sources.</p>
        </div>
        <form method="POST" action="<?= url('admin/scraper/run') ?>">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-primary">▶️ Run Scraper Now</button>
        </form>
    </div>

    <!-- Sources -->
    <section class="dash-card" style="margin-bottom:1.25rem">
        <h3 class="dash-card-title">📰 Default News Sources</h3>
        <?php
        $sources = [
            ['NDTV Politics',     'ndtv.com',           '🟢'],
            ['India Today',       'indiatoday.in',      '🔵'],
            ['The Hindu',         'thehindu.com',       '🟠'],
            ['Times of India',    'timesofindia.com',   '🟡'],
            ['Hindustan Times',   'hindustantimes.com', '🟣'],
            ['News18 Politics',   'news18.com',         '🔴'],
        ];
        foreach ($sources as [$name, $domain, $dot]): ?>
        <div class="scraper-card">
            <div class="scraper-info">
                <h4><?= $dot ?> <?= $name ?></h4>
                <p><?= $domain ?> &mdash; RSS Feed</p>
            </div>
            <span class="job-status job-completed">Active</span>
        </div>
        <?php endforeach; ?>
    </section>

    <!-- Recent Jobs -->
    <section class="dash-card">
        <h3 class="dash-card-title">📄 Recent Scraper Jobs</h3>
        <?php if (empty($jobs)): ?>
        <div class="empty"><div class="empty-icon">🤖</div><p>No scraper jobs yet. Run the scraper to see results.</p></div>
        <?php else: ?>
        <table class="admin-table">
            <thead><tr><th>Source</th><th>Status</th><th>Items</th><th>Started</th><th>Error</th></tr></thead>
            <tbody>
            <?php foreach ($jobs as $job): ?>
            <tr>
                <td><?= e($job['source_name']) ?></td>
                <td><span class="job-status job-<?= $job['status'] ?>"><?= ucfirst($job['status']) ?></span></td>
                <td><?= $job['items_found'] ?></td>
                <td style="font-size:.8rem;color:var(--text-3)"><?= $job['started_at'] ? timeAgo($job['started_at']) : '—' ?></td>
                <td style="font-size:.75rem;color:#f87171"><?= $job['error_msg'] ? truncate($job['error_msg'],60) : '' ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </section>
</div>
