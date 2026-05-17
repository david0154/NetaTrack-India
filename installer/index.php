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
function php_ok(): bool { return version_compare(PHP_VERSION, '8.1', '>='); }

$siteUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
          . '://' . $_SERVER['HTTP_HOST'];

/**
 * Run a .sql file using mysqli multi_query.
 * Returns error string or empty string on success.
 */
function run_sql_file(string $host, int $port, string $user, string $pass,
                      string $db, string $file): string {
    if (!file_exists($file)) return "File not found: $file";
    $mysqli = new mysqli($host, $user, $pass, $db, $port);
    if ($mysqli->connect_errno) return 'Connect error: ' . $mysqli->connect_error;
    $mysqli->set_charset('utf8mb4');
    $sql = file_get_contents($file);
    if (!$mysqli->multi_query($sql)) {
        $err = $mysqli->error;
        $mysqli->close();
        return "SQL error in $file: $err";
    }
    do {
        if ($res = $mysqli->store_result()) $res->free();
    } while ($mysqli->more_results() && $mysqli->next_result());
    $err = $mysqli->error;
    $mysqli->close();
    return $err ?: '';
}

/**
 * Check if a column exists using INFORMATION_SCHEMA.
 * Works on MySQL 5.6, 5.7, 8.0 — all shared hosting versions.
 */
function col_exists(mysqli $db, string $dbName, string $table, string $col): bool {
    $res = $db->query(
        "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS
          WHERE TABLE_SCHEMA = '" . $db->real_escape_string($dbName) . "'
            AND TABLE_NAME   = '" . $db->real_escape_string($table)  . "'
            AND COLUMN_NAME  = '" . $db->real_escape_string($col)    . "'
         LIMIT 1"
    );
    return $res && $res->num_rows > 0;
}

/**
 * Check if an index exists.
 */
function idx_exists(mysqli $db, string $dbName, string $table, string $idx): bool {
    $res = $db->query(
        "SELECT 1 FROM INFORMATION_SCHEMA.STATISTICS
          WHERE TABLE_SCHEMA = '" . $db->real_escape_string($dbName) . "'
            AND TABLE_NAME   = '" . $db->real_escape_string($table)  . "'
            AND INDEX_NAME   = '" . $db->real_escape_string($idx)    . "'
         LIMIT 1"
    );
    return $res && $res->num_rows > 0;
}

/**
 * Patch any existing DB to have all columns the seed needs.
 * Uses INFORMATION_SCHEMA to check before ALTER — no MySQL 8+ syntax needed.
 */
function patch_schema(string $host, int $port, string $user, string $pass,
                      string $dbName): string {
    $db = new mysqli($host, $user, $pass, $dbName, $port);
    if ($db->connect_errno) return 'Patch connect error: ' . $db->connect_error;
    $db->set_charset('utf8mb4');

    // [ table, column, column_definition ]
    $cols = [
        // parties
        ['parties', 'color',              "VARCHAR(20) NOT NULL DEFAULT '#3B82F6'"],

        // states
        ['states',  'type',               "ENUM('state','ut') NOT NULL DEFAULT 'state'"],
        ['states',  'total_seats',        "INT NOT NULL DEFAULT 0"],
        ['states',  'capital',            "VARCHAR(100) DEFAULT NULL"],
        ['states',  'region',             "VARCHAR(80) DEFAULT NULL"],

        // leaders
        ['leaders', 'slug',               "VARCHAR(200) DEFAULT NULL"],
        ['leaders', 'constituency',       "VARCHAR(200) DEFAULT NULL"],
        ['leaders', 'role',               "VARCHAR(200) DEFAULT NULL"],
        ['leaders', 'photo_url',          "VARCHAR(500) DEFAULT NULL"],
        ['leaders', 'dob',                "DATE DEFAULT NULL"],
        ['leaders', 'education',          "TEXT DEFAULT NULL"],
        ['leaders', 'total_score',        "DECIMAL(5,2) NOT NULL DEFAULT 0"],
        ['leaders', 'attendance_score',   "DECIMAL(5,2) NOT NULL DEFAULT 0"],
        ['leaders', 'promise_score',      "DECIMAL(5,2) NOT NULL DEFAULT 0"],
        ['leaders', 'criminal_score',     "DECIMAL(5,2) NOT NULL DEFAULT 0"],
        ['leaders', 'fund_score',         "DECIMAL(5,2) NOT NULL DEFAULT 0"],
        ['leaders', 'transparency_score', "DECIMAL(5,2) NOT NULL DEFAULT 0"],
        ['leaders', 'verified',           "TINYINT(1) NOT NULL DEFAULT 0"],
        ['leaders', 'status',             "ENUM('active','inactive','banned') NOT NULL DEFAULT 'active'"],
    ];

    foreach ($cols as [$tbl, $col, $def]) {
        if (!col_exists($db, $dbName, $tbl, $col)) {
            if (!$db->query("ALTER TABLE `$tbl` ADD COLUMN `$col` $def")) {
                // Error 1060 = duplicate column (race condition) — safe to ignore
                if ($db->errno !== 1060) {
                    $err = $db->error;
                    $db->close();
                    return "Patch failed ($tbl.$col): $err";
                }
            }
        }
    }

    // Unique index on leaders.slug
    if (!idx_exists($db, $dbName, 'leaders', 'idx_leaders_slug')) {
        // Ignore error 1061 (duplicate key name)
        $db->query("ALTER TABLE `leaders` ADD UNIQUE INDEX `idx_leaders_slug` (`slug`)");
    }

    $db->close();
    return '';
}

// ---- Process Step 2 form ----------------------------------------
if ($step === 2 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = trim($_POST['db_host']  ?? 'localhost');
    $dbPort = (int)($_POST['db_port'] ?? 3306);
    $dbName = trim($_POST['db_name']  ?? '');
    $dbUser = trim($_POST['db_user']  ?? '');
    $dbPass = trim($_POST['db_pass']  ?? '');
    $aName  = trim($_POST['admin_name']  ?? 'Admin');
    $aEmail = trim($_POST['admin_email'] ?? '');
    $aPass  = trim($_POST['admin_pass']  ?? '');

    if (!$dbName || !$dbUser)    $error = 'Database name and username are required.';
    elseif (!$aEmail)            $error = 'Admin email is required.';
    elseif (strlen($aPass) < 6) $error = 'Admin password must be at least 6 characters.';
    else {
        try {
            // A: Create database
            $pdo0 = new PDO(
                "mysql:host=$dbHost;port=$dbPort;charset=utf8mb4",
                $dbUser, $dbPass,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            $pdo0->exec("CREATE DATABASE IF NOT EXISTS `$dbName`
                         CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo0 = null;

            // B: Run schema.sql
            $e = run_sql_file($dbHost,$dbPort,$dbUser,$dbPass,$dbName,
                              $root.'/installer/schema.sql');
            if ($e) throw new RuntimeException('Schema error: '.$e);

            // C: Patch missing columns (INFORMATION_SCHEMA check — MySQL 5.6/5.7/8 safe)
            $e = patch_schema($dbHost,$dbPort,$dbUser,$dbPass,$dbName);
            if ($e) throw new RuntimeException($e);

            // D: Run seed
            $e = run_sql_file($dbHost,$dbPort,$dbUser,$dbPass,$dbName,
                              $root.'/database/seed_states_leaders.sql');
            if ($e) throw new RuntimeException('Seed error: '.$e);

            // E: PDO for settings + admin user
            $pdo = new PDO(
                "mysql:host=$dbHost;port=$dbPort;dbname=$dbName;charset=utf8mb4",
                $dbUser, $dbPass,
                [
                    PDO::ATTR_ERRMODE                  => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES         => true,
                    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                ]
            );

            $token = gen_token();

            file_put_contents($root.'/.env', implode("\n", [
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
            ])."\n");

            $ins = $pdo->prepare(
                "INSERT IGNORE INTO settings (`key`,value,type,group_name,label)
                 VALUES (?,?,'string','general',?)"
            );
            foreach ([
                ['admin_api_token',$token,'Admin API Token'],
                ['site_url',$siteUrl,'Site URL'],
                ['site_name','NetaTrack India','Site Name'],
            ] as [$k,$v,$l]) {
                try { $ins->execute([$k,$v,$l]); } catch (PDOException $e) {}
            }

            $pdo->prepare(
                "INSERT IGNORE INTO users (name,email,password_hash,role_id)
                 VALUES (?,?,?,1)"
            )->execute([$aName,$aEmail,password_hash($aPass,PASSWORD_BCRYPT)]);

            $step = 3;

        } catch (PDOException $e) {
            $error = 'Database error: '.$e->getMessage();
        } catch (Throwable $e) {
            $error = 'Error: '.$e->getMessage();
        }
    }
}

// ---- Requirements -----------------------------------------------
$reqs = [
    ['PHP 8.1+',     php_ok(),                        PHP_VERSION, true],
    ['PDO MySQL',    extension_loaded('pdo_mysql'),   'required',  true],
    ['mysqli',       extension_loaded('mysqli'),      'required',  true],
    ['cURL',         extension_loaded('curl'),        'optional',  false],
    ['mbstring',     extension_loaded('mbstring'),    'optional',  false],
    ['Folder write', is_writable($root)||is_writable($root.'/.env'), 'needed', true],
];
$allOk = !in_array(false, array_map(fn($r)=>$r[1]||!$r[3], $reqs));
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
.head{padding:28px 32px 20px;text-align:center;border-bottom:1px solid #1e293b}
.head img{width:52px;border-radius:10px;margin:0 auto 10px;display:block}
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
.btn2:hover{color:#f8fafc}
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
    <?php foreach (['Requirements','Setup','Done'] as $i=>$s): ?>
    <div class="step <?= $step===$i+1?'active':($step>$i+1?'done':'') ?>">
      <span class="n"><?= $step>$i+1?'✓':$i+1 ?></span><?= $s ?>
    </div>
    <?php endforeach ?>
  </div>

  <div class="body">

  <?php if ($step===1): ?>
    <h3>Server Requirements</h3>
    <?php foreach ($reqs as [$label,$ok,$note,$req]): ?>
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
      <?php if ($allOk): ?>
        <a href="?step=2" class="btn">Continue →</a>
      <?php else: ?>
        <div class="alert err">Fix the required items above first.</div>
        <a href="" class="btn" style="background:#1e293b;color:#94a3b8;border:1px solid #334155">🔄 Re-check</a>
      <?php endif ?>
    </div>

  <?php elseif ($step===2): ?>
    <?php if ($error): ?>
    <div class="alert err"><?= htmlspecialchars($error) ?></div>
    <?php endif ?>
    <form method="POST" action="?step=2">
      <h3>🗄️ Database <span style="font-weight:400">— cPanel → MySQL Databases</span></h3>
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

  <?php elseif ($step===3): ?>
    <div style="text-align:center;padding:8px 0 18px">
      <div style="font-size:3rem;margin-bottom:8px">🎉</div>
      <h2 style="margin-bottom:6px">Installation Complete!</h2>
      <p style="color:#64748b;font-size:.9rem">NetaTrack India is ready.</p>
    </div>
    <div class="alert ok">
      ✓ Database &amp; all tables created<br>
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
      Python app → .env → API_KEY=paste here
    </div>
    <div class="btns">
      <a href="/admin" class="btn2 primary">🏗 Admin Panel</a>
      <a href="/" class="btn2">🌐 View Site</a>
    </div>
    <div class="alert warn" style="margin-top:14px;font-size:.82rem">
      ⚠️ Delete the
      <code style="background:#1e293b;padding:1px 5px;border-radius:3px">installer/</code>
      folder after this!
    </div>
  <?php endif ?>
  </div>

  <div class="foot">
    NetaTrack India &mdash; <a href="https://github.com/david0154/NetaTrack-India">GitHub</a>
  </div>
</div>
</body>
</html>
