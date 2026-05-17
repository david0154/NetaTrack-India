<?php
/**
 * NetaTrack India — Web Installer
 * Visit: https://yoursite.com/install.php
 */

define('NT_INSTALL', true);
$step    = (int)($_GET['step'] ?? 1);
$error   = '';
$success = '';
$baseDir = dirname(__DIR__);

function php_ver_ok(): bool { return version_compare(PHP_VERSION,'8.1','>='); }

function write_env(array $d, string $path): void {
    $lines = [];
    foreach ($d as $k => $v) $lines[] = "$k=" . $v;
    file_put_contents($path, implode("\n", $lines) . "\n");
}

function gen_token(int $len = 48): string {
    return bin2hex(random_bytes($len / 2));
}

// Auto-detect site URL
$siteUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
         . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');

// ---- Step 2: Process form ---------------------------------------
if ($step === 2 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $host        = trim($_POST['db_host'] ?? 'localhost');
    $port        = (int)($_POST['db_port'] ?? 3306);
    $name        = trim($_POST['db_name'] ?? '');
    $user        = trim($_POST['db_user'] ?? '');
    $pass        = trim($_POST['db_pass'] ?? '');
    $adminName   = trim($_POST['admin_name']  ?? 'Admin');
    $adminEmail  = trim($_POST['admin_email'] ?? '');
    $adminPass   = trim($_POST['admin_pass']  ?? '');

    // Validate
    if (!$name || !$user)        $error = 'Database name and username are required.';
    elseif (!$adminEmail)        $error = 'Admin email is required.';
    elseif (strlen($adminPass) < 6) $error = 'Admin password must be at least 6 characters.';
    else {
        try {
            $pdo = new PDO(
                "mysql:host=$host;port=$port;charset=utf8mb4",
                $user, $pass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            // Create DB
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$name`");

            // Run schema
            $schemaFile = $baseDir . '/database/schema.sql';
            if (file_exists($schemaFile)) {
                foreach (array_filter(array_map('trim', explode(';', file_get_contents($schemaFile)))) as $stmt)
                    try { if($stmt) $pdo->exec($stmt); } catch(PDOException $e){}
            }

            // Run seed
            $seedFile = $baseDir . '/database/seed_states_leaders.sql';
            if (file_exists($seedFile)) {
                foreach (array_filter(array_map('trim', explode(';', file_get_contents($seedFile)))) as $stmt)
                    try { if($stmt && !str_starts_with($stmt,'--')) $pdo->exec($stmt); } catch(PDOException $e){}
            }

            // Generate API token
            $token = gen_token();

            // Write .env
            write_env([
                'APP_NAME'        => 'NetaTrack India',
                'APP_URL'         => $siteUrl,
                'APP_ENV'         => 'production',
                'DB_HOST'         => $host,
                'DB_PORT'         => $port,
                'DB_NAME'         => $name,
                'DB_USER'         => $user,
                'DB_PASS'         => $pass,
                'ADMIN_API_TOKEN' => $token,
            ], $baseDir . '/.env');

            // Insert default settings
            $ins = $pdo->prepare("INSERT IGNORE INTO settings (key_name, value) VALUES (?,?)");
            foreach ([
                ['site_name',       'NetaTrack India'],
                ['site_url',        $siteUrl],
                ['admin_api_token', $token],
                ['gemini_key',      ''],
                ['openai_key',      ''],
                ['sarvam_key',      ''],
            ] as [$k,$v]) try { $ins->execute([$k,$v]); } catch(PDOException $e){}

            // Create admin user
            $pdo->prepare(
                "INSERT IGNORE INTO users (name, email, password, role, status) VALUES (?,?,?,'admin','active')"
            )->execute([$adminName, $adminEmail, password_hash($adminPass, PASSWORD_BCRYPT)]);

            $step    = 3;
            $success = $token;

        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

// ---- Requirements -----------------------------------------------
$reqs = [
    ['PHP 8.1+',      php_ver_ok(),                    PHP_VERSION,  true],
    ['PDO MySQL',     extension_loaded('pdo_mysql'),   'required',   true],
    ['cURL',          extension_loaded('curl'),        'recommended',false],
    ['mbstring',      extension_loaded('mbstring'),    'recommended',false],
    ['OpenSSL',       extension_loaded('openssl'),     'recommended',false],
    ['Folder write',  is_writable($baseDir) || is_writable($baseDir.'/.env'), 'needed', true],
];
$allOk = !in_array(false, array_map(fn($r) => $r[1] || !$r[3], $reqs));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Install — NetaTrack India</title>
<link rel="stylesheet" href="assets/css/app.css">
<link rel="stylesheet" href="assets/css/installer.css">
</head>
<body>
<div class="india-stripe"></div>

<div class="installer-wrap">
<div class="installer-box">

  <!-- Header -->
  <div class="installer-header">
    <img src="assets/../logo.png" alt="" onerror="this.style.display='none'" style="width:56px;margin:0 auto 12px;border-radius:10px">
    <h1 style="font-size:1.6rem">🇮🇳 NetaTrack India</h1>
    <p style="margin:0">Web Installer — 3 simple steps</p>
  </div>

  <!-- Steps -->
  <div style="padding:20px 32px 0">
    <div class="install-steps">
      <?php foreach (['Requirements', 'Setup', 'Done'] as $i => $s): ?>
      <div class="install-step <?= $step===$i+1 ? 'active' : ($step>$i+1 ? 'done' : '') ?>">
        <span class="step-num"><?= $step > $i+1 ? '✓' : $i+1 ?></span>
        <?= $s ?>
      </div>
      <?php endforeach ?>
    </div>
  </div>

  <div class="installer-body">

  <?php if ($step === 1): /* ===== STEP 1: Requirements ===== */ ?>

    <h3 style="margin-bottom:14px">Server Requirements</h3>

    <?php foreach ($reqs as [$label, $ok, $note, $required]): ?>
    <div class="req-item">
      <span class="req-label">
        <?= htmlspecialchars($label) ?>
        <span class="text-xs text-muted">(<?= htmlspecialchars($note) ?>)</span>
      </span>
      <span class="<?= $ok ? 'req-ok' : ($required ? 'req-fail' : 'req-warn') ?>">
        <?= $ok ? '✓ OK' : ($required ? '✗ Missing' : '⚠ Optional') ?>
      </span>
    </div>
    <?php endforeach ?>

    <div style="margin-top:20px">
      <?php if ($allOk): ?>
        <a href="?step=2" class="btn btn-primary btn-lg btn-block">Continue →</a>
      <?php else: ?>
        <div class="alert alert-error">Please fix the missing required items above.</div>
        <a href="" class="btn btn-ghost btn-block">🔄 Re-check</a>
      <?php endif ?>
    </div>

  <?php elseif ($step === 2): /* ===== STEP 2: Setup Form ===== */ ?>

    <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif ?>

    <form method="POST" action="?step=2">

      <!-- DB Section -->
      <div style="margin-bottom:6px;font-weight:700;font-size:.9rem;color:var(--text-secondary)">
        🗄️ Database
        <span class="text-xs text-muted" style="font-weight:400"> — from cPanel → MySQL Databases</span>
      </div>

      <div class="form-group">
        <label class="form-label">Database Host</label>
        <input class="form-control" name="db_host" value="localhost" placeholder="localhost">
        <span class="form-hint">Almost always <strong>localhost</strong> on shared hosting</span>
      </div>

      <div class="form-group">
        <label class="form-label">Database Name</label>
        <input class="form-control" name="db_name" placeholder="e.g. u123456_netatrack" required>
      </div>

      <div class="grid-2">
        <div class="form-group">
          <label class="form-label">DB Username</label>
          <input class="form-control" name="db_user" placeholder="e.g. u123456_admin" required>
        </div>
        <div class="form-group">
          <label class="form-label">DB Password</label>
          <input class="form-control" type="password" name="db_pass" placeholder="••••••••">
        </div>
      </div>

      <hr style="border-color:var(--border);margin:18px 0 16px">

      <!-- Admin Section -->
      <div style="margin-bottom:12px;font-weight:700;font-size:.9rem;color:var(--text-secondary)">
        👤 Admin Account
      </div>

      <div class="form-group">
        <label class="form-label">Your Name</label>
        <input class="form-control" name="admin_name" placeholder="e.g. David" value="Admin">
      </div>

      <div class="form-group">
        <label class="form-label">Admin Email</label>
        <input class="form-control" type="email" name="admin_email" placeholder="you@example.com" required>
        <span class="form-hint">Used to log in to admin panel</span>
      </div>

      <div class="form-group">
        <label class="form-label">Admin Password</label>
        <input class="form-control" type="password" name="admin_pass"
               placeholder="Min 6 characters" required minlength="6">
      </div>

      <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top:4px">
        🚀 Install Now
      </button>
    </form>

  <?php elseif ($step === 3): /* ===== STEP 3: Done ===== */ ?>

    <div style="text-align:center;padding:8px 0 20px">
      <div style="font-size:3.5rem;margin-bottom:10px">🎉</div>
      <h2 style="margin-bottom:6px">Installation Complete!</h2>
      <p style="margin:0">NetaTrack India is ready to use.</p>
    </div>

    <div class="alert alert-success">
      ✓ Database tables created<br>
      ✓ All 36 States &amp; UTs seeded<br>
      ✓ 45+ current leaders seeded<br>
      ✓ Admin account created<br>
      ✓ API token generated
    </div>

    <!-- API Token -->
    <div class="form-group" style="margin-top:16px">
      <label class="form-label">🔑 Admin API Token <span class="text-muted">(save this!)</span></label>
      <div class="token-display">
        <span id="tok"><?= htmlspecialchars($success) ?></span>
        <button class="copy-btn" onclick="copyText(document.getElementById('tok').textContent,'API Token')">Copy</button>
      </div>
      <span class="form-hint">Paste this into the Python app → Settings → Push API Token</span>
    </div>

    <div class="grid-2" style="margin-top:20px">
      <a href="/admin" class="btn btn-primary btn-lg">🏛 Admin Panel</a>
      <a href="/"      class="btn btn-ghost  btn-lg">🌐 View Site</a>
    </div>

    <div class="alert alert-warning" style="margin-top:16px;font-size:.85rem">
      ⚠️ Delete <code>install.php</code> from your server after this step!
    </div>

  <?php endif ?>
  </div>

  <div class="installer-footer">
    NetaTrack India &mdash; <a href="https://github.com/david0154/NetaTrack-India">GitHub</a>
  </div>

</div>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>
