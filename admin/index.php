<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Repository.php';

Auth::start();
if (isset($_GET['logout'])) {
    Auth::logout();
    header('Location: /admin/index.php');
    exit;
}

$error = null;
$flash = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['password'])) {
    if (!Auth::login($_POST['email'], $_POST['password'])) {
        $error = 'Invalid email or password';
    }
}

if (!Auth::check()) {
    ?>
    <!doctype html><html><body style="font-family:Arial;max-width:420px;margin:30px auto;">
    <h1>Admin Login</h1>
    <?php if ($error): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
    <form method="post" style="display:grid;gap:8px;">
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
    </form>
    </body></html>
    <?php exit;
}

$user = Auth::user();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        if ($_POST['action'] === 'approve_submission') {
            Repository::setSubmissionStatus((int)$_POST['id'], 'approved');
            $flash = 'Submission approved.';
        }
        if ($_POST['action'] === 'reject_submission') {
            Repository::setSubmissionStatus((int)$_POST['id'], 'rejected');
            $flash = 'Submission rejected.';
        }
        if ($_POST['action'] === 'create_promise') {
            Repository::createPromise([
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description']),
                'state' => trim($_POST['state']),
                'category' => trim($_POST['category']),
                'budget' => trim($_POST['budget']),
                'deadline' => trim($_POST['deadline']),
                'status' => trim($_POST['status']),
                'verification_score' => trim($_POST['verification_score']),
                'source_name' => trim($_POST['source_name']),
                'source_url' => trim($_POST['source_url']),
                'created_by' => $user['id'] ?? null,
            ]);
            $flash = 'Promise created.';
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

try {
    $stats = Repository::stats();
    $submissions = Repository::latestSubmissions(15);
    $aiQueue = Repository::latestAiQueue(15);
    $promises = Repository::latestPromises(15);
} catch (Throwable $e) {
    $error = $e->getMessage();
    $stats = ['total_promises' => 0, 'pending_submissions' => 0, 'pending_ai' => 0, 'published_promises' => 0];
    $submissions = $aiQueue = $promises = [];
}
?>
<!doctype html><html><body style="font-family:Arial;max-width:1100px;margin:20px auto;">
<h1>Admin Moderation Panel</h1>
<p>Welcome, <?= htmlspecialchars($user['name'] ?? 'Admin') ?> | <a href="/public/index.php">Public Site</a> | <a href="?logout=1">Logout</a></p>
<?php if ($flash): ?><p style="color:green;"><?= htmlspecialchars($flash) ?></p><?php endif; ?>
<?php if ($error): ?><p style="color:red;">Error: <?= htmlspecialchars($error) ?></p><?php endif; ?>
<div style="display:flex;gap:20px;flex-wrap:wrap;">
<div><h3>Total Promises</h3><p><?= $stats['total_promises'] ?></p></div>
<div><h3>Published</h3><p><?= $stats['published_promises'] ?></p></div>
<div><h3>Pending Submissions</h3><p><?= $stats['pending_submissions'] ?></p></div>
<div><h3>Pending AI Queue</h3><p><?= $stats['pending_ai'] ?></p></div>
</div>

<h2>Create Promise (Admin Manual Upload)</h2>
<form method="post" style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
<input type="hidden" name="action" value="create_promise">
<input name="title" placeholder="Title" required>
<input name="state" placeholder="State" required>
<input name="category" placeholder="Category" required>
<input name="budget" placeholder="Budget">
<input name="deadline" placeholder="YYYY-MM-DD">
<input name="status" value="pending" required>
<input name="verification_score" value="70" required>
<input name="source_name" placeholder="Source Name" required>
<input name="source_url" placeholder="Source URL" required>
<textarea name="description" placeholder="Description" required style="grid-column:1/3;"></textarea>
<button type="submit" style="grid-column:1/3;">Create Promise</button>
</form>

<h2>Public Submissions Queue</h2>
<table border="1" cellpadding="6" cellspacing="0"><tr><th>ID</th><th>Title</th><th>Leader</th><th>State</th><th>Status</th><th>Actions</th></tr>
<?php foreach ($submissions as $row): ?>
<tr>
<td><?= (int)$row['id'] ?></td><td><?= htmlspecialchars($row['title']) ?></td><td><?= htmlspecialchars($row['leader_name']) ?></td><td><?= htmlspecialchars($row['state']) ?></td><td><?= htmlspecialchars($row['status']) ?></td>
<td>
<form method="post" style="display:inline;"><input type="hidden" name="action" value="approve_submission"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><button type="submit">Approve</button></form>
<form method="post" style="display:inline;"><input type="hidden" name="action" value="reject_submission"><input type="hidden" name="id" value="<?= (int)$row['id'] ?>"><button type="submit">Reject</button></form>
</td>
</tr>
<?php endforeach; ?>
</table>

<h2>AI Collection Queue</h2>
<table border="1" cellpadding="6" cellspacing="0"><tr><th>ID</th><th>Source</th><th>Title</th><th>Score</th><th>Status</th></tr>
<?php foreach ($aiQueue as $row): ?><tr><td><?= (int)$row['id'] ?></td><td><?= htmlspecialchars($row['source_name']) ?></td><td><?= htmlspecialchars($row['title']) ?></td><td><?= (int)$row['ai_score'] ?></td><td><?= htmlspecialchars($row['status']) ?></td></tr><?php endforeach; ?>
</table>

<h2>Latest Promises</h2>
<table border="1" cellpadding="6" cellspacing="0"><tr><th>ID</th><th>Title</th><th>State</th><th>Category</th><th>Status</th><th>Score</th></tr>
<?php foreach ($promises as $row): ?><tr><td><?= (int)$row['id'] ?></td><td><?= htmlspecialchars($row['title']) ?></td><td><?= htmlspecialchars($row['state']) ?></td><td><?= htmlspecialchars($row['category']) ?></td><td><?= htmlspecialchars($row['status']) ?></td><td><?= (int)$row['verification_score'] ?></td></tr><?php endforeach; ?>
</table>
</body></html>
