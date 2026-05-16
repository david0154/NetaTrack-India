<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/Database.php';
$stats = ['promises' => 0, 'submissions' => 0, 'ai_pending' => 0];
$error = null;
try {
    $pdo = Database::connection();
    $stats['promises'] = (int)$pdo->query('SELECT COUNT(*) FROM promises')->fetchColumn();
    $stats['submissions'] = (int)$pdo->query("SELECT COUNT(*) FROM public_submissions WHERE status='pending'")->fetchColumn();
    $stats['ai_pending'] = (int)$pdo->query("SELECT COUNT(*) FROM ai_collected_data WHERE status='pending_review'")->fetchColumn();
} catch (Throwable $e) {
    $error = $e->getMessage();
}
?>
<!doctype html><html><body style="font-family:Arial;max-width:900px;margin:20px auto;">
<h1>Admin Moderation Panel</h1>
<p><a href="/public/index.php">Back to site</a></p>
<?php if ($error): ?><p style="color:red;">DB error: <?= htmlspecialchars($error) ?></p><?php endif; ?>
<div style="display:flex;gap:20px;">
<div><h3>Total Promises</h3><p><?= $stats['promises'] ?></p></div>
<div><h3>Pending Public Submissions</h3><p><?= $stats['submissions'] ?></p></div>
<div><h3>Pending AI Queue</h3><p><?= $stats['ai_pending'] ?></p></div>
</div>
</body></html>
