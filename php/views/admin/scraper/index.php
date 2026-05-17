<div class="grid grid-2">
    <section class="panel">
        <div class="panel__head"><h2>Scraper Sources</h2></div>
        <table class="table">
            <thead><tr><th>Name</th><th>URL</th><th>Type</th><th>Active</th></tr></thead>
            <tbody>
            <?php foreach($sources as $source): ?>
                <tr>
                    <td><?= htmlspecialchars($source['name']) ?></td>
                    <td class="truncate"><?= htmlspecialchars($source['url']) ?></td>
                    <td><?= htmlspecialchars($source['source_type']) ?></td>
                    <td><?= (int)$source['is_active'] ? 'Yes' : 'No' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <form method="post" action="/admin/scraper/start">
            <input type="hidden" name="_token" value="<?= $this->csrf() ?>">
            <button class="btn btn--primary" type="submit">Run Scraper Queue</button>
        </form>
    </section>

    <section class="panel">
        <div class="panel__head"><h2>AI Approval Queue</h2></div>
        <table class="table">
            <thead><tr><th>Title</th><th>Source</th><th>Confidence</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach($queue as $item): ?>
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
</div>
