<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/Database.php';

$message = null;
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('INSERT INTO public_submissions (title, leader_name, state, description, source_link, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([
            trim($_POST['title'] ?? ''),
            trim($_POST['leader_name'] ?? ''),
            trim($_POST['state'] ?? ''),
            trim($_POST['description'] ?? ''),
            trim($_POST['source_link'] ?? ''),
            'pending',
        ]);
        $message = 'Submission received. Status: Pending Review.';
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!doctype html><html><body style="font-family:Arial;background:#0f172a;color:white;max-width:900px;margin:20px auto;">
<h1>Public Report Submission</h1>
<p><a href="/public/index.php" style="color:#93c5fd;">Back to Home</a></p>
<?php if($message): ?><p style="color:#86efac;"><?= htmlspecialchars($message) ?></p><?php endif; ?>
<?php if($error): ?><p style="color:#fca5a5;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post" style="display:grid;gap:8px;max-width:650px;">
<input name="title" placeholder="Title" required>
<input name="leader_name" placeholder="Leader Name" required>
<input name="state" placeholder="State" required>
<input name="source_link" placeholder="Source URL" required>
<textarea name="description" placeholder="Description" required></textarea>
<button type="submit">Submit</button>
</form>
</body></html>
