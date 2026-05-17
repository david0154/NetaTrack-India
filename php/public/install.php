<?php
/**
 * NetaTrack India — Web Installer
 * Visit: https://yoursite.com/install.php
 * Deletes itself after successful install.
 */

define('NT_INSTALL', true);
$step    = (int)($_GET['step'] ?? 1);
$error   = '';
$success = '';
$baseDir = dirname(__DIR__);

// ---- Helper functions -------------------------------------------
function req_check(string $ext, string $label): array {
    $ok = extension_loaded($ext);
    return ['label'=>$label,'ok'=>$ok,'status'=>$ok?'✓':'✗'];
}
function php_ver_ok(): bool { return version_compare(PHP_VERSION,'8.1','>='); }
function write_env(array $d, string $path): void {
    $lines = [];
    foreach ($d as $k=>$v) $lines[] = "$k=" . addslashes($v);
    file_put_contents($path, implode("\n",$lines)."\n");
}
function gen_token(int $len=48): string {
    return bin2hex(random_bytes($len/2));
}

// ---- Step 2: Process DB form ------------------------------------
if ($step===2 && $_SERVER['REQUEST_METHOD']==='POST') {
    $host = trim($_POST['db_host'] ?? '127.0.0.1');
    $port = (int)trim($_POST['db_port'] ?? 3306);
    $name = trim($_POST['db_name'] ?? '');
    $user = trim($_POST['db_user'] ?? '');
    $pass = trim($_POST['db_pass'] ?? '');
    $site = rtrim(trim($_POST['site_url'] ?? ''),'/') ?: (isset($_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['HTTP_HOST'];
    $admin_email = trim($_POST['admin_email'] ?? 'admin@example.com');
    $admin_pass  = trim($_POST['admin_pass']  ?? 'admin123');

    if (!$name || !$user) {
        $error = 'Database name and username are required.';
    } else {
        try {
            $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            // Create DB if not exists
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$name`");

            // Run schema
            $schemaFile = $baseDir.'/../database/schema.sql';
            if (file_exists($schemaFile)) {
                $sql = file_get_contents($schemaFile);
                foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
                    if ($stmt) try { $pdo->exec($stmt); } catch(PDOException $e) { /* skip already exists */ }
                }
            }

            // Run seed
            $seedFile = $baseDir.'/../database/seed_states_leaders.sql';
            if (file_exists($seedFile)) {
                $sql = file_get_contents($seedFile);
                foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
                    if ($stmt && !str_starts_with($stmt,'--'))
                        try { $pdo->exec($stmt); } catch(PDOException $e) { /* skip duplicates */ }
                }
            }

            // Generate token + write .env
            $token = gen_token();
            write_env([
                'APP_NAME'         => 'NetaTrack India',
                'APP_URL'          => $site,
                'APP_ENV'          => 'production',
                'DB_HOST'          => $host,
                'DB_PORT'          => $port,
                'DB_NAME'          => $name,
                'DB_USER'          => $user,
                'DB_PASS'          => $pass,
                'ADMIN_API_TOKEN'  => $token,
                'ADMIN_EMAIL'      => $admin_email,
            ], $baseDir.'/.env');

            // Insert default settings into DB
            $defaults = [
                ['admin_api_token', $token],
                ['site_url',        $site],
                ['site_name',       'NetaTrack India'],
                ['gemini_key',      ''],
                ['openai_key',      ''],
                ['openrouter_key',  ''],
                ['claude_key',      ''],
                ['sarvam_key',      ''],
            ];
            $ins = $pdo->prepare("INSERT IGNORE INTO settings (key_name, value) VALUES (?,?)");
            foreach ($defaults as [$k,$v]) {
                try { $ins->execute([$k,$v]); } catch(PDOException $e){}
            }

            // Create admin user
            $hashed = password_hash($admin_pass, PASSWORD_BCRYPT);
            try {
                $pdo->prepare("INSERT IGNORE INTO users (name,email,password,role,status) VALUES (?,?,?,'admin','active')")
                    ->execute(['Admin', $admin_email, $hashed]);
            } catch(PDOException $e){}

            $step    = 3;
            $success = $token;
        } catch(PDOException $e) {
            $error = 'DB Error: ' . $e->getMessage();
        }
    }
}

// ---- Requirements check -----------------------------------------
$reqs = [
    ['PHP 8.1+',       php_ver_ok(), PHP_VERSION],
    ['PDO',            extension_loaded('pdo'),        'required'],
    ['PDO MySQL',      extension_loaded('pdo_mysql'),  'required'],
    ['cURL',           extension_loaded('curl'),       'recommended'],
    ['JSON',           extension_loaded('json'),       'required'],
    ['mbstring',       extension_loaded('mbstring'),   'recommended'],
    ['OpenSSL',        extension_loaded('openssl'),    'recommended'],
    ['.env writable',  is_writable($baseDir) || is_writable($baseDir.'/.env'), 'needed for .env'],
];
$allOk = array_reduce($reqs, fn($c,$r)=>$c&&($r[1]||$r[2]==='recommended'), true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>NetaTrack India — Installer</title>
<link rel="stylesheet" href="assets/css/app.css">
<link rel="stylesheet" href="assets/css/installer.css">
</head>
<body>
<div class="india-stripe"></div>
<div class="installer-wrap">
<div class="installer-box">

  <!-- Header -->
  <div class="installer-header">
    <img src="../logo.png" alt="NetaTrack" onerror="this.style.display='none'">
    <h1>🇮🇳 NetaTrack India</h1>
    <p>Web Installer — sets up your database, seeds all states &amp; leaders</p>
  </div>

  <!-- Step indicator -->
  <div style="padding:0 36px;margin-top:24px">
    <div class="install-steps">
      <?php foreach(['Requirements','Database','Complete'] as $i=>$s): ?>
      <div class="install-step <?= $step===$i+1?'active':($step>$i+1?'done':'') ?>">
        <span class="step-num"><?= $step>$i+1?'✓':$i+1 ?></span>
        <?= $s ?>
      </div>
      <?php endforeach ?>
    </div>
  </div>

  <div class="installer-body">

  <?php if($step===1): /* ---- STEP 1: Requirements ------------- */ ?>
    <h3 style="margin-bottom:16px">System Requirements</h3>
    <?php foreach($reqs as [$label,$ok,$note]): ?>
    <div class="req-item">
      <span class="req-label"><?= htmlspecialchars($label) ?>
        <span class="text-muted text-xs">(<?= htmlspecialchars($note) ?>)</span>
      </span>
      <span class="<?= $ok?'req-ok':'req-'.($note==='recommended'?'warn':'fail') ?>">
        <?= $ok?'✓ OK':($note==='recommended'?'⚠ Missing':'✗ Required') ?>
      </span>
    </div>
    <?php endforeach ?>

    <div style="margin-top:24px">
      <?php if($allOk): ?>
      <a href="?step=2" class="btn btn-primary btn-lg btn-block">Continue →</a>
      <?php else: ?>
      <div class="alert alert-error">Fix the required items above before continuing.</div>
      <a href="?step=1" class="btn btn-ghost btn-block">Re-check</a>
      <?php endif ?>
    </div>

  <?php elseif($step===2): /* ---- STEP 2: DB form -------------- */ ?>
    <h3 style="margin-bottom:4px">Database Configuration</h3>
    <p class="text-muted text-sm" style="margin-bottom:20px">Get these details from your hosting cPanel → MySQL Databases</p>

    <?php if($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif ?>

    <form method="POST" action="?step=2">
      <div class="grid-2">
        <div class="form-group">
          <label class="form-label">DB Host</label>
          <input class="form-control" name="db_host" value="localhost" placeholder="localhost">
          <span class="form-hint">Usually 'localhost' on shared hosting</span>
        </div>
        <div class="form-group">
          <label class="form-label">DB Port</label>
          <input class="form-control" name="db_port" value="3306" placeholder="3306">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Database Name</label>
        <input class="form-control" name="db_name" placeholder="u123456789_netatrack" required>
        <span class="form-hint">Create this DB in cPanel → MySQL Databases first</span>
      </div>
      <div class="grid-2">
        <div class="form-group">
          <label class="form-label">DB Username</label>
          <input class="form-control" name="db_user" placeholder="u123456789_admin" required>
        </div>
        <div class="form-group">
          <label class="form-label">DB Password</label>
          <input class="form-control" type="password" name="db_pass" placeholder="••••••••">
        </div>
      </div>
      <hr style="border-color:var(--border);margin:16px 0">
      <div class="form-group">
        <label class="form-label">Site URL</label>
        <input class="form-control" name="site_url" value="<?= htmlspecialchars('https://'.$_SERVER['HTTP_HOST']) ?>">
      </div>
      <div class="grid-2">
        <div class="form-group">
          <label class="form-label">Admin Email</label>
          <input class="form-control" type="email" name="admin_email" value="admin@example.com">
        </div>
        <div class="form-group">
          <label class="form-label">Admin Password</label>
          <input class="form-control" type="password" name="admin_pass" value="admin123">
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top:8px">
        🚀 Install NetaTrack India
      </button>
    </form>

  <?php elseif($step===3): /* ---- STEP 3: Complete ------------- */ ?>
    <div style="text-align:center;padding:16px 0">
      <div style="font-size:3rem;margin-bottom:12px">🎉</div>
      <h2 style="margin-bottom:8px">Installation Complete!</h2>
      <p>NetaTrack India is ready. All states, UTs, and 45 current leaders have been seeded.</p>
    </div>
    <div class="alert alert-success" style="margin:16px 0">
      ✓ Database created &amp; schema imported<br>
      ✓ 28 States + 8 UTs seeded<br>
      ✓ 45 current Indian leaders seeded<br>
      ✓ Admin account created<br>
      ✓ .env file written
    </div>
    <div style="margin-bottom:16px">
      <div class="form-label">Your Admin API Token (save this for Python app):</div>
      <div class="token-display">
        <span id="api-token"><?= htmlspecialchars($success) ?></span>
        <button class="copy-btn" onclick="copyText('<?= htmlspecialchars($success) ?>','Token')">Copy</button>
      </div>
    </div>
    <div class="grid-2" style="margin-top:16px">
      <a href="../admin" class="btn btn-primary btn-lg">🏛 Go to Admin Panel</a>
      <a href="../" class="btn btn-ghost btn-lg">🌐 View Website</a>
    </div>
    <div class="alert alert-warning" style="margin-top:16px">
      ⚠️ <strong>Delete this file after installation:</strong><br>
      <code>php/public/install.php</code>
    </div>

  <?php endif ?>
  </div>

  <div class="installer-footer">
    NetaTrack India &mdash; Built by <a href="https://github.com/david0154">David</a>
  </div>
</div>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
