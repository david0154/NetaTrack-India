<div class="grid grid-2">
    <section class="panel">
        <div class="panel__head"><h2>Daily Traffic</h2></div>
        <table class="table">
            <thead><tr><th>Date</th><th>Visits</th></tr></thead>
            <tbody>
            <?php foreach($daily as $row): ?>
                <tr><td><?= htmlspecialchars($row['d']) ?></td><td><?= (int)$row['total'] ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>

    <section class="panel">
        <div class="panel__head"><h2>Popular Pages</h2></div>
        <table class="table">
            <thead><tr><th>Page</th><th>Hits</th></tr></thead>
            <tbody>
            <?php foreach($popular as $row): ?>
                <tr><td><?= htmlspecialchars($row['page']) ?></td><td><?= (int)$row['total'] ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>
