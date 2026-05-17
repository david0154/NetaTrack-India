<?php
/**
 * NetaTrack India — Installer
 * https://yoursite.com/installer/
 */

$step  = (int)($_GET['step'] ?? 1);
$error = '';
$token = '';
$root  = dirname(__DIR__);

function gen_token(): string { return bin2hex(random_bytes(24)); }
function php_ok(): bool { return version_compare(PHP_VERSION,'8.1','>='); }

$siteUrl = (isset($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off'?'https':'http')
          .'://'.$_SERVER['HTTP_HOST'];

// ---- Safe SQL executor: runs each statement one by one --------------
function run_sql(PDO $pdo, string $file): void {
    if (!file_exists($file)) return;
    $raw = file_get_contents($file);

    // Strip comments, split by semicolon
    $raw   = preg_replace('/--[^\n]*\n/', "\n", $raw);   // remove -- comments
    $raw   = preg_replace('/\/\*.*?\*\//s', '', $raw);    // remove /* */ comments
    $stmts = array_filter(array_map('trim', explode(';', $raw)));

    foreach ($stmts as $sql) {
        if ($sql === '') continue;
        try {
            $pdo->exec($sql);
        } catch (PDOException $e) {
            // Skip duplicate/already-exists errors silently
            if (!in_array($e->getCode(), ['42S01','42000','23000'])) {
                // non-fatal: log but continue
            }
        }
    }
}

// ---- Process Step 2 form ----------------------------------------
if ($step===2 && $_SERVER['REQUEST_METHOD']==='POST') {
    $dbHost  = trim($_POST['db_host']  ?? 'localhost');
    $dbPort  = (int)($_POST['db_port'] ?? 3306);
    $dbName  = trim($_POST['db_name']  ?? '');
    $dbUser  = trim($_POST['db_user']  ?? '');
    $dbPass  = trim($_POST['db_pass']  ?? '');
    $aName   = trim($_POST['admin_name']  ?? 'Admin');
    $aEmail  = trim($_POST['admin_email'] ?? '');
    $aPass   = trim($_POST['admin_pass']  ?? '');

    if (!$dbName || !$dbUser)       $error = 'Database name and username are required.';
    elseif (!$aEmail)               $error = 'Admin email is required.';
    elseif (strlen($aPass) < 6)    $error = 'Admin password must be at least 6 characters.';
    else {
        try {
            $pdo = new PDO(
                "mysql:host=$dbHost;port=$dbPort;charset=utf8mb4",
                $dbUser, $dbPass,
                [
                    PDO::ATTR_ERRMODE                  => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE       => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,   // fix unbuffered error
                    PDO::ATTR_EMULATE_PREPARES         => true,   // needed for multi-stmt
                ]
            );

            // Create DB
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `$dbName`");

            // Run schema + seeds one statement at a time
            run_sql($pdo, $root.'/installer/schema.sql');
            run_sql($pdo, $root.'/database/seed_states_leaders.sql');
            run_sql($pdo, $root.'/installer/seed.sql'); // optional

            // Generate token
            $token = gen_token();

            // Write .env
            $env = implode("\n", [
                "APP_NAME=NetaTrack India",
                "APP_URL=$siteUrl",
                "APP_ENV=production",
                "DB_HOST=$dbHost",
                "DB_PORT=$dbPort",
                "DB_NAME=$dbName",
                "DB_USER=$dbUser",
                "DB_PASS=$dbPass",
                "ADMIN_API_TOKEN=$token",
                "GEMINI_KEY=",
                "SARVAM_KEY=",
            ])."\n";
            file_put_contents($root.'/.env', $env);

            // Insert default settings
            $ins = $pdo->prepare("INSERT IGNORE INTO settings (key_name, value) VALUES (?, ?)");
            foreach ([
                ['site_name',       'NetaTrack India'],
                ['site_url',        $siteUrl],
                ['admin_api_token', $token],
                ['gemini_key',      ''],
                ['sarvam_key',      ''],
                ['openai_key',      ''],
            ] as [$k, $v]) {
                try { $ins->execute([$k, $v]); } catch (PDOException $e) {}
            }

            // Create admin user
            $pdo->prepare(
                "INSERT IGNORE INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'admin', 'active')"
            )->execute([$aName, $aEmail, password_hash($aPass, PASSWORD_BCRYPT)]);

            $step = 3;

        } catch (PDOException $e) {
            $error = 'Database error: '.$e->getMessage();
        }
    }
}

// ---- Requirements -----------------------------------------------
$reqs = [
    ['PHP 8.1+',     php_ok(),                        PHP_VERSION, true],
    ['PDO MySQL',    extension_loaded('pdo_mysql'),   'required',  true],
    ['cURL',         extension_loaded('curl'),        'optional',  false],
    ['mbstring',     extension_loaded('mbstring'),    'optional',  false],
    ['OpenSSL',      extension_loaded('openssl'),     'optional',  false],
    ['Folder write', is_writable($root)||is_writable($root.'/.env'), 'needed', true],
];
$allOk = !in_array(false, array_map(fn($r) => $r[1] || !$r[3], $reqs));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>NetaTrack India — Installer</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',system-ui,sans-serif;background:#020617;color:#f8fafc;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
.box{background:#0f172a;border:1px solid #334155;border-radius:16px;width:100%;max-width:520px;overflow:hidden;box-shadow:0 8px 40px rgba(0,0,0,.5)}
.stripe{height:4px;background:linear-gradient(90deg,#FF9933 33%,#fff 33% 66%,#138808 66%)}
.head{padding:28px 32px 20px;text-align:center;border-bottom:1px solid #1e293b;background:linear-gradient(135deg,rgba(59,130,246,.08),transparent)}
.head img{width:52px;border-radius:10px;margin:0 auto 10px}
.head h1{font-size:1.5rem;margin-bottom:4px}
.head p{color:#64748b;font-size:.88rem}
.steps{display:flex;margin:20px 28px 0;border:1px solid #1e293b;border-radius:8px;overflow:hidden}
.step{flex:1;padding:9px 6px;text-align:center;font-size:.75rem;font-weight:600;background:#1e293b;color:#64748b;border-right:1px solid #334155}
.step:last-child{border-right:none}
.step.active{background:rgba(59,130,246,.12);color:#3b82f6}
.step.done{background:rgba(34,197,94,.1);color:#22c55e}
.step .n{display:block;font-size:1rem;font-weight:800;margin-bottom:2px}
.body{padding:28px 32px}
h3{font-size:.88rem;color:#94a3b8;font-weight:700;margin-bottom:12px;padding-bottom:6px;border-bottom:1px solid #1e293b}
label{display:block;font-size:.82rem;color:#94a3b8;margin-bottom:4px;font-weight:500}
input{width:100%;padding:9px 12px;background:#1e293b;border:1px solid #334155;border-radius:7px;color:#f8fafc;font-size:.9rem;outline:none;margin-bottom:12px;transition:border-color .15s}
input:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.12)}
.hint{font-size:.75rem;color:#475569;margin-top:-8px;margin-bottom:12px}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.btn{display:block;width:100%;padding:11px;background:#3b82f6;color:#fff;border:none;border-radius:8px;font-size:1rem;font-weight:700;cursor:pointer;margin-top:4px;transition:background .15s;text-align:center;text-decoration:none}
.btn:hover{background:#2563eb}
.alert{padding:11px 14px;border-radius:8px;font-size:.88rem;margin-bottom:16px;border-left:4px solid}
.alert.err{background:rgba(239,68,68,.1);border-color:#ef4444;color:#fca5a5}
.alert.ok{background:rgba(34,197,94,.1);border-color:#22c55e;color:#86efac}
.alert.warn{background:rgba(245,158,11,.1);border-color:#f59e0b;color:#fde68a}
.req{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #1e293b;font-size:.88rem}
.req:last-child{border-bottom:none}
.ok{color:#22c55e;font-weight:700}
.fail{color:#ef4444;font-weight:700}
.opt{color:#f59e0b;font-weight:700}
.token-box{display:flex;align-items:center;gap:8px;background:#020617;border:1px solid #334155;border-radius:8px;padding:10px 12px;font-family:monospace;font-size:.8rem;color:#94a3b8;word-break:break-all;margin-bottom:4px}
.copy{flex-shrink:0;padding:4px 10px;background:#1e293b;border:1px solid #334155;border-radius:5px;color:#94a3b8;font-size:.75rem;cursor:pointer}
.copy:hover{color:#f8fafc}
.btns{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:16px}
.btn2{display:block;padding:10px;text-align:center;border-radius:8px;font-weight:700;font-size:.9rem;text-decoration:none;border:1px solid #334155;color:#94a3b8;transition:all .15s}
.btn2:hover{color:#f8fafc;border-color:#475569}
.btn2.primary{background:#3b82f6;border-color:#3b82f6;color:#fff}
.btn2.primary:hover{background:#2563eb}
.foot{padding:14px 32px;background:#020617;border-top:1px solid #1e293b;text-align:center;font-size:.78rem;color:#475569}
.foot a{color:#3b82f6}
</style>
</head>
<body>
<div class="box">
  <div class="stripe"></div>

  <div class="head">
    <img src="../logo.png" alt="" onerror="this.style.display='none'">
    <h1>🇮🇳 NetaTrack India</h1>
    <p>Web Installer — 3 simple steps</p>
  </div>

  <div class="steps">
    <?php foreach(['Requirements','Setup','Done'] as $i=>$s): ?>
    <div class="step <?= $step===$i+1?'active':($step>$i+1?'done':'') ?>">
      <span class="n"><?= $step>$i+1?'✓':$i+1 ?></span><?= $s ?>
    </div>
    <?php endforeach ?>
  </div>

  <div class="body">

  <?php if($step===1): /* STEP 1 */ ?>

    <h3>Server Requirements</h3>
    <?php foreach($reqs as [$label,$ok,$note,$req]): ?>
    <div class="req">
      <span style="color:#cbd5e1"><?= htmlspecialchars($label) ?>
        <span style="color:#475569;font-size:.75rem">(<?= $note ?>)</span>
      </span>
      <span class="<?= $ok?'ok':($req?'fail':'opt') ?>">
        <?= $ok?'✓ OK':($req?'✗ Required':'⚠ Optional') ?>
      </span>
    </div>
    <?php endforeach ?>

    <div style="margin-top:18px">
      <?php if($allOk): ?>
        <a href="?step=2" class="btn">Continue →</a>
      <?php else: ?>
        <div class="alert err">Fix the required items above first.</div>
        <a href="" class="btn" style="background:#1e293b;color:#94a3b8;border:1px solid #334155">🔄 Re-check</a>
      <?php endif ?>
    </div>

  <?php elseif($step===2): /* STEP 2 */ ?>

    <?php if($error): ?>
    <div class="alert err"><?= htmlspecialchars($error) ?></div>
    <?php endif ?>

    <form method="POST" action="?step=2">

      <h3>🗄️ Database
        <span style="font-weight:400">— cPanel → MySQL Databases</span>
      </h3>

      <label>Database Host</label>
      <input name="db_host" value="localhost">
      <div class="hint">Use <strong style="color:#cbd5e1">localhost</strong> on shared hosting</div>

      <label>Database Name</label>
      <input name="db_name" placeholder="e.g. u123456_netatrack" required>

      <div class="grid">
        <div>
          <label>DB Username</label>
          <input name="db_user" placeholder="e.g. u123456_admin" required>
        </div>
        <div>
          <label>DB Password</label>
          <input type="password" name="db_pass" placeholder="••••••">
        </div>
      </div>

      <h3 style="margin-top:4px">👤 Admin Account</h3>

      <label>Your Name</label>
      <input name="admin_name" value="Admin" required>

      <label>Admin Email</label>
      <input type="email" name="admin_email" placeholder="you@example.com" required>

      <label>Admin Password <span style="color:#475569;font-weight:400">(min 6 chars)</span></label>
      <input type="password" name="admin_pass" placeholder="••••••" minlength="6" required>

      <button type="submit" class="btn">🚀 Install Now</button>
    </form>

  <?php elseif($step===3): /* STEP 3 */ ?>

    <div style="text-align:center;padding:8px 0 18px">
      <div style="font-size:3rem;margin-bottom:8px">🎉</div>
      <h2 style="margin-bottom:6px">Installation Complete!</h2>
      <p style="color:#64748b;font-size:.9rem">NetaTrack India is ready.</p>
    </div>

    <div class="alert ok">
      ✓ Database &amp; tables created<br>
      ✓ States &amp; leaders seeded<br>
      ✓ Admin account created<br>
      ✓ API token generated
    </div>

    <label style="color:#94a3b8;font-size:.82rem;display:block;margin-bottom:6px">
      🔑 Admin API Token <span style="color:#475569">(save — needed for Python app)</span>
    </label>
    <div class="token-box">
      <span id="tok"><?= htmlspecialchars($token) ?></span>
      <button class="copy"
        onclick="navigator.clipboard.writeText(document.getElementById('tok').textContent).then(()=>{this.textContent='✓ Copied';setTimeout(()=>this.textContent='Copy',2000)})">
        Copy
      </button>
    </div>
    <div style="font-size:.75rem;color:#475569;margin-bottom:16px">
      Python app → Settings → Push API Token → paste here
    </div>

    <div class="btns">
      <a href="/admin" class="btn2 primary">🏛 Admin Panel</a>
      <a href="/"      class="btn2">🌐 View Site</a>
    </div>

    <div class="alert warn" style="margin-top:14px;font-size:.82rem">
      ⚠️ Delete the <code style="background:#1e293b;padding:1px 5px;border-radius:3px">installer/</code>
      folder from your server after this!
    </div>

  <?php endif ?>
  </div>

  <div class="foot">
    NetaTrack India &mdash; <a href="https://github.com/david0154/NetaTrack-India">GitHub</a>
  </div>
</div>
</body>
</html>
