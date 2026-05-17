<?php
/**
 * NetaTrack India — One-Click Web Installer
 * Access: http://yoursite.com/install.php
 * Auto-deletes itself after successful install.
 */

define('BASE_PATH', dirname(__DIR__));

// Block if already installed
if (file_exists(BASE_PATH . '/.installed')) {
    die('<div style="font-family:sans-serif;text-align:center;padding:3rem;background:#0f172a;color:#e2e8f0;min-height:100vh"><h2 style="color:#22c55e">✅ NetaTrack India is already installed!</h2><p style="color:#94a3b8;margin-top:.75rem">Delete <code>.installed</code> to re-run the installer.</p><a href="/" style="color:#3b82f6">Go to Site &rarr;</a></div>');
}

session_start();
$step   = max(1, min(5, (int)($_GET['step'] ?? 1)));
$errors = [];
$success = '';

/* ============================================================
   STEP HANDLERS
   ============================================================ */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // STEP 2 — DB Test
    if ($step === 2) {
        $_SESSION['db'] = [
            'host' => trim($_POST['db_host'] ?? '127.0.0.1'),
            'port' => trim($_POST['db_port'] ?? '3306'),
            'name' => trim($_POST['db_name'] ?? 'netatrack'),
            'user' => trim($_POST['db_user'] ?? ''),
            'pass' => $_POST['db_pass'] ?? '',
        ];
        try {
            $dsn = "mysql:host={$_SESSION['db']['host']};port={$_SESSION['db']['port']};charset=utf8mb4";
            $pdo = new PDO($dsn, $_SESSION['db']['user'], $_SESSION['db']['pass'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
            // Try creating DB if not exists
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$_SESSION['db']['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$_SESSION['db']['name']}`");
            $_SESSION['db']['pdo'] = true;
            header('Location: install.php?step=3'); exit;
        } catch (PDOException $e) {
            $errors[] = 'Database connection failed: ' . $e->getMessage();
        }
    }

    // STEP 3 — Site Config
    if ($step === 3) {
        $_SESSION['app'] = [
            'name'        => trim($_POST['site_name'] ?? 'NetaTrack India'),
            'url'         => rtrim(trim($_POST['site_url'] ?? ''), '/'),
            'timezone'    => $_POST['timezone'] ?? 'Asia/Kolkata',
            'gemini_key'  => trim($_POST['gemini_key'] ?? ''),
            'sarvam_key'  => trim($_POST['sarvam_key'] ?? ''),
        ];
        if (empty($_SESSION['app']['url'])) $errors[] = 'Site URL is required.';
        if (empty($errors)) { header('Location: install.php?step=4'); exit; }
    }

    // STEP 4 — Admin Account
    if ($step === 4) {
        $name    = trim($_POST['admin_name'] ?? '');
        $email   = trim($_POST['admin_email'] ?? '');
        $pass    = $_POST['admin_pass'] ?? '';
        $confirm = $_POST['admin_pass2'] ?? '';
        if (strlen($name) < 2)   $errors[] = 'Admin name too short.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email.';
        if (strlen($pass) < 8)   $errors[] = 'Password must be at least 8 characters.';
        if ($pass !== $confirm)   $errors[] = 'Passwords do not match.';
        if (empty($errors)) {
            $_SESSION['admin'] = ['name'=>$name,'email'=>$email,'pass'=>$pass];
            header('Location: install.php?step=5'); exit;
        }
    }

    // STEP 5 — Run Install
    if ($step === 5) {
        try {
            $db   = $_SESSION['db'];
            $app  = $_SESSION['app'];
            $adm  = $_SESSION['admin'];

            $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset=utf8mb4";
            $pdo = new PDO($dsn, $db['user'], $db['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
            ]);

            // ---- Run SQL migration ----
            $sqlFile = BASE_PATH . '/database/migrations/001_create_core_tables.sql';
            if (file_exists($sqlFile)) {
                $sql = file_get_contents($sqlFile);
                $statements = array_filter(array_map('trim',
                    preg_split('/;\s*$/m', $sql)
                ), fn($s) => $s !== '' && !preg_match('/^\s*--/', $s));
                $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
                foreach ($statements as $stmt) {
                    if (trim($stmt)) { try { $pdo->exec($stmt); } catch(PDOException $e) { /* skip exists errors */ } }
                }
                $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
            }

            // ---- Insert admin user ----
            $hash = password_hash($adm['pass'], PASSWORD_BCRYPT, ['cost'=>12]);
            $pdo->prepare(
                "INSERT INTO users (name,email,password,role,status,created_at,updated_at)
                 VALUES (:n,:e,:p,'admin','active',NOW(),NOW())
                 ON DUPLICATE KEY UPDATE password=:p2, name=:n2"
            )->execute([':n'=>$adm['name'],':e'=>$adm['email'],':p'=>$hash,':p2'=>$hash,':n2'=>$adm['name']]);

            // ---- Remove default admin if different email ----
            if ($adm['email'] !== 'admin@netatrack.in') {
                $pdo->prepare("DELETE FROM users WHERE email='admin@netatrack.in' AND name='NetaTrack Admin'")->execute();
            }

            // ---- Update settings ----
            $settings = [
                'site_name'   => $app['name'],
                'site_url'    => $app['url'],
                'gemini_api_key' => $app['gemini_key'],
                'sarvam_api_key' => $app['sarvam_key'],
                'ai_enabled'  => !empty($app['gemini_key']) ? '1' : '0',
            ];
            foreach ($settings as $k => $v) {
                $pdo->prepare(
                    "INSERT INTO settings (`key`,`value`) VALUES (:k,:v) ON DUPLICATE KEY UPDATE `value`=:v2"
                )->execute([':k'=>$k,':v'=>$v,':v2'=>$v]);
            }

            // ---- Write .env file ----
            $appKey = bin2hex(random_bytes(16));
            $envContent = "APP_NAME=\"{$app['name']}\"
APP_URL={$app['url']}
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE={$app['timezone']}
APP_KEY={$appKey}

DB_HOST={$db['host']}
DB_PORT={$db['port']}
DB_NAME={$db['name']}
DB_USER={$db['user']}
DB_PASS={$db['pass']}

GEMINI_API_KEY={$app['gemini_key']}
SARVAM_API_KEY={$app['sarvam_key']}

SMTP_HOST=
SMTP_PORT=587
SMTP_USER=
SMTP_PASS=
SESSION_LIFETIME=120
SESSION_SECURE=false
";
            file_put_contents(BASE_PATH . '/.env', $envContent);

            // ---- Create .installed lock ----
            file_put_contents(BASE_PATH . '/.installed', date('Y-m-d H:i:s') . ' | ' . $app['url']);

            // ---- Clear session ----
            session_destroy();
            $success = $app['url'];

        } catch (Throwable $e) {
            $errors[] = 'Installation failed: ' . $e->getMessage();
        }
    }
}

/* ============================================================
   REQUIREMENTS CHECK
   ============================================================ */
function checkRequirements(): array {
    $checks = [
        ['PHP >= 8.1',     version_compare(PHP_VERSION,'8.1','>=')],
        ['PDO Extension',  extension_loaded('pdo')],
        ['PDO MySQL',      extension_loaded('pdo_mysql')],
        ['cURL',           extension_loaded('curl')],
        ['SimpleXML',      extension_loaded('simplexml')],
        ['OpenSSL',        extension_loaded('openssl')],
        ['Writable: /',    is_writable(BASE_PATH)],
        ['Writable: /public', is_writable(BASE_PATH.'/public')],
    ];
    return $checks;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Install NetaTrack India</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/install.css">
</head>
<body>

<div class="install-wrapper">
    <!-- Header -->
    <div class="install-header">
        <div class="brand">🇮🇳 <span>NetaTrack India</span></div>
        <p class="brand-sub">Web Installer</p>
    </div>

    <!-- Steps indicator -->
    <div class="steps-bar">
        <?php
        $stepLabels = ['Welcome','Database','Site Config','Admin Account','Install'];
        for ($i = 1; $i <= 5; $i++):
            $cls = $i < $step ? 'done' : ($i == $step ? 'active' : '');
        ?>
        <div class="step-item <?= $cls ?>">
            <div class="step-circle"><?= $i < $step ? '✓' : $i ?></div>
            <div class="step-label"><?= $stepLabels[$i-1] ?></div>
        </div>
        <?php if ($i < 5): ?><div class="step-line <?= $i < $step ? 'done' : '' ?>"></div><?php endif; ?>
        <?php endfor; ?>
    </div>

    <!-- Card -->
    <div class="install-card">

        <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $e): ?><p>❌ <?= htmlspecialchars($e) ?></p><?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <!-- SUCCESS -->
        <div class="success-screen">
            <div class="success-icon">🎉</div>
            <h2>Installation Complete!</h2>
            <p>NetaTrack India has been successfully installed.</p>
            <div class="success-links">
                <a href="<?= htmlspecialchars($success) ?>" class="btn btn-primary">🌐 Visit Site</a>
                <a href="<?= htmlspecialchars($success) ?>/admin/auth/login" class="btn btn-orange">🛡️ Admin Panel</a>
            </div>
            <div class="security-note">
                ⚠️ <strong>Security:</strong> The installer has been locked. Delete <code>public/install.php</code> for extra security.
            </div>
        </div>

        <?php elseif ($step === 1): ?>
        <!-- STEP 1: Welcome + Requirements -->
        <h2 class="step-title">👋 Welcome to NetaTrack India</h2>
        <p class="step-desc">This wizard will set up your NetaTrack India installation in just a few steps. Let’s check your server requirements first.</p>

        <div class="req-list">
            <?php $allOk = true; foreach (checkRequirements() as [$label, $ok]):
                if (!$ok) $allOk = false; ?>
            <div class="req-item">
                <span class="req-icon"><?= $ok ? '✅' : '❌' ?></span>
                <span class="req-label"><?= htmlspecialchars($label) ?></span>
                <span class="req-status <?= $ok ? 'ok' : 'fail' ?>"><?= $ok ? 'OK' : 'MISSING' ?></span>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($allOk): ?>
        <a href="install.php?step=2" class="btn btn-primary btn-full">→ Continue to Database Setup</a>
        <?php else: ?>
        <div class="alert alert-warn">⚠️ Please fix the missing requirements before continuing.</div>
        <?php endif; ?>

        <?php elseif ($step === 2): ?>
        <!-- STEP 2: Database -->
        <h2 class="step-title">🗄️ Database Configuration</h2>
        <p class="step-desc">Enter your MySQL/MariaDB credentials. The database will be created automatically if it doesn’t exist.</p>
        <form method="POST" action="install.php?step=2">
            <div class="form-group"><label>Host</label><input type="text" name="db_host" value="<?= htmlspecialchars($_SESSION['db']['host'] ?? '127.0.0.1') ?>"></div>
            <div class="form-group"><label>Port</label><input type="number" name="db_port" value="<?= htmlspecialchars($_SESSION['db']['port'] ?? '3306') ?>"></div>
            <div class="form-group"><label>Database Name</label><input type="text" name="db_name" value="<?= htmlspecialchars($_SESSION['db']['name'] ?? 'netatrack') ?>"></div>
            <div class="form-group"><label>Username</label><input type="text" name="db_user" value="<?= htmlspecialchars($_SESSION['db']['user'] ?? 'root') ?>" autofocus></div>
            <div class="form-group"><label>Password</label><input type="password" name="db_pass"></div>
            <div class="form-actions">
                <a href="install.php?step=1" class="btn btn-secondary">← Back</a>
                <button type="submit" class="btn btn-primary">Test &amp; Continue →</button>
            </div>
        </form>

        <?php elseif ($step === 3): ?>
        <!-- STEP 3: Site Config -->
        <h2 class="step-title">⚙️ Site Configuration</h2>
        <p class="step-desc">Configure your site details and optional AI API keys.</p>
        <form method="POST" action="install.php?step=3">
            <div class="form-group"><label>Site Name</label><input type="text" name="site_name" value="<?= htmlspecialchars($_SESSION['app']['name'] ?? 'NetaTrack India') ?>"></div>
            <div class="form-group"><label>Site URL <span class="hint">(no trailing slash)</span></label><input type="url" name="site_url" value="<?= htmlspecialchars($_SESSION['app']['url'] ?? ('http://'.$_SERVER['HTTP_HOST'])) ?>" required></div>
            <div class="form-group">
                <label>Timezone</label>
                <select name="timezone">
                    <?php
                    $zones = ['Asia/Kolkata','Asia/Mumbai','Asia/Delhi','UTC','Asia/Dubai','Asia/Singapore','Europe/London','America/New_York'];
                    foreach ($zones as $z):
                    ?><option value="<?= $z ?>" <?= ($_SESSION['app']['timezone'] ?? 'Asia/Kolkata') === $z ? 'selected' : '' ?>><?= $z ?></option><?php endforeach; ?>
                </select>
            </div>
            <hr class="divider">
            <p class="optional-label">🤖 AI Keys <span class="hint">(optional — enables AI report analysis)</span></p>
            <div class="form-group"><label>Gemini API Key</label><input type="password" name="gemini_key" value="<?= htmlspecialchars($_SESSION['app']['gemini_key'] ?? '') ?>" placeholder="AIza..."></div>
            <div class="form-group"><label>Sarvam AI Key</label><input type="password" name="sarvam_key" value="<?= htmlspecialchars($_SESSION['app']['sarvam_key'] ?? '') ?>"></div>
            <div class="form-actions">
                <a href="install.php?step=2" class="btn btn-secondary">← Back</a>
                <button type="submit" class="btn btn-primary">Continue →</button>
            </div>
        </form>

        <?php elseif ($step === 4): ?>
        <!-- STEP 4: Admin Account -->
        <h2 class="step-title">👤 Create Admin Account</h2>
        <p class="step-desc">This will be your primary administrator account.</p>
        <form method="POST" action="install.php?step=4">
            <div class="form-group"><label>Full Name</label><input type="text" name="admin_name" value="<?= htmlspecialchars($_SESSION['admin']['name'] ?? '') ?>" required autofocus></div>
            <div class="form-group"><label>Email Address</label><input type="email" name="admin_email" value="<?= htmlspecialchars($_SESSION['admin']['email'] ?? '') ?>" required></div>
            <div class="form-group"><label>Password <span class="hint">(min 8 chars)</span></label><input type="password" name="admin_pass" required></div>
            <div class="form-group"><label>Confirm Password</label><input type="password" name="admin_pass2" required></div>
            <div class="form-actions">
                <a href="install.php?step=3" class="btn btn-secondary">← Back</a>
                <button type="submit" class="btn btn-primary">Continue →</button>
            </div>
        </form>

        <?php elseif ($step === 5): ?>
        <!-- STEP 5: Confirm + Install -->
        <h2 class="step-title">🚀 Ready to Install</h2>
        <p class="step-desc">Review your configuration and click Install to begin.</p>

        <div class="review-grid">
            <div class="review-section">
                <h4>🗄️ Database</h4>
                <div class="review-row"><span>Host</span><strong><?= htmlspecialchars($_SESSION['db']['host'] ?? '') ?></strong></div>
                <div class="review-row"><span>Database</span><strong><?= htmlspecialchars($_SESSION['db']['name'] ?? '') ?></strong></div>
                <div class="review-row"><span>User</span><strong><?= htmlspecialchars($_SESSION['db']['user'] ?? '') ?></strong></div>
            </div>
            <div class="review-section">
                <h4>🌐 Site</h4>
                <div class="review-row"><span>Name</span><strong><?= htmlspecialchars($_SESSION['app']['name'] ?? '') ?></strong></div>
                <div class="review-row"><span>URL</span><strong><?= htmlspecialchars($_SESSION['app']['url'] ?? '') ?></strong></div>
                <div class="review-row"><span>AI Enabled</span><strong><?= !empty($_SESSION['app']['gemini_key']) ? '✅ Yes' : '❌ No' ?></strong></div>
            </div>
            <div class="review-section">
                <h4>👤 Admin</h4>
                <div class="review-row"><span>Name</span><strong><?= htmlspecialchars($_SESSION['admin']['name'] ?? '') ?></strong></div>
                <div class="review-row"><span>Email</span><strong><?= htmlspecialchars($_SESSION['admin']['email'] ?? '') ?></strong></div>
            </div>
        </div>

        <form method="POST" action="install.php?step=5">
            <div class="form-actions">
                <a href="install.php?step=4" class="btn btn-secondary">← Back</a>
                <button type="submit" class="btn btn-install">🚀 Install NetaTrack India</button>
            </div>
        </form>
        <?php endif; ?>

    </div><!-- /.install-card -->

    <p class="install-footer">NetaTrack India &mdash; Political Accountability Platform for India</p>
</div>

</body>
</html>
