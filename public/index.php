<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/EnvValidator.php';

$missingEnv = EnvValidator::required(['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER']);
$dbReady = true;
$promises = [];
try {
    $pdo = Database::connection();
    $promises = $pdo->query('SELECT id,title,state,status,verification_score FROM promises ORDER BY id DESC LIMIT 8')->fetchAll();
} catch (Throwable $e) {
    $dbReady = false;
}
?>
<!doctype html><html><body style="font-family:Arial;background:#0f172a;color:white;max-width:900px;margin:20px auto;">
<h1>NetaTrack India (PHP)</h1>
<p>Automatic AI Collection + Public Submissions + Admin Manual Upload.</p>
<p>
<a href="/admin/index.php" style="color:#93c5fd;">Open Admin Panel</a> |
<a href="/public/submit.php" style="color:#93c5fd;">Submit Public Report</a> |
<a href="/installer/index.php" style="color:#93c5fd;">Run Installer</a>
</p>
<?php if ($missingEnv): ?><p style="color:#fbbf24;">Missing ENV: <?= htmlspecialchars(implode(', ', $missingEnv)) ?></p><?php endif; ?>
<?php if(!$dbReady): ?><p style="color:#fbbf24;">Database not configured. Run installer first.</p><?php endif; ?>
<h2>Latest Promises</h2>
<ul>
<?php foreach ($promises as $p): ?>
<li><?= htmlspecialchars($p['title']) ?> (<?= htmlspecialchars($p['state']) ?>) - <?= htmlspecialchars($p['status']) ?> - Score <?= (int)$p['verification_score'] ?>%</li>
<?php endforeach; ?>
</ul>
</body></html>
