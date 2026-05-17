<?php
/**
 * NetaTrack India — One-Click Web Installer
 */

define('BASE_PATH', dirname(__DIR__));

if (file_exists(BASE_PATH . '/.installed')) {
    die('<div style="font-family:sans-serif;text-align:center;padding:3rem;background:#0f172a;color:#e2e8f0;min-height:100vh"><h2 style="color:#22c55e">✅ NetaTrack India is already installed!</h2><p style="color:#94a3b8;margin-top:.75rem">Delete <code>.installed</code> to re-run the installer.</p><a href="/" style="color:#3b82f6">Go to Site &rarr;</a></div>');
}

session_start();
$step   = max(1, min(6, (int)($_GET['step'] ?? 1)));
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$_SESSION['db']['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$_SESSION['db']['name']}`");
            header('Location: install.php?step=3'); exit;
        } catch (PDOException $e) {
            $errors[] = 'Database connection failed: ' . $e->getMessage();
        }
    }

    if ($step === 3) {
        $_SESSION['app'] = [
            'name'              => trim($_POST['site_name'] ?? 'NetaTrack India'),
            'url'               => rtrim(trim($_POST['site_url'] ?? ''), '/'),
            'timezone'          => $_POST['timezone'] ?? 'Asia/Kolkata',
            'site_tagline'      => trim($_POST['site_tagline'] ?? ''),
            'meta_description'  => trim($_POST['meta_description'] ?? ''),
            'logo_url'          => trim($_POST['logo_url'] ?? ''),
            'gemini_key'        => trim($_POST['gemini_key'] ?? ''),
            'sarvam_key'        => trim($_POST['sarvam_key'] ?? ''),
        ];
        if (empty($_SESSION['app']['url'])) $errors[] = 'Site URL is required.';
        if (empty($errors)) { header('Location: install.php?step=4'); exit; }
    }

    if ($step === 4) {
        $_SESSION['site_features'] = [
            'google_analytics_id' => trim($_POST['google_analytics_id'] ?? ''),
            'google_adsense_code' => trim($_POST['google_adsense_code'] ?? ''),
            'sponsor_title'       => trim($_POST['sponsor_title'] ?? ''),
            'sponsor_html'        => trim($_POST['sponsor_html'] ?? ''),
            'announcement_text'   => trim($_POST['announcement_text'] ?? ''),
            'announcement_link'   => trim($_POST['announcement_link'] ?? ''),
            'announcement_active' => !empty($_POST['announcement_active']) ? '1' : '0',
        ];
        header('Location: install.php?step=5'); exit;
    }

    if ($step === 5) {
        $name    = trim($_POST['admin_name'] ?? '');
        $email   = trim($_POST['admin_email'] ?? '');
        $pass    = $_POST['admin_pass'] ?? '';
        $confirm = $_POST['admin_pass2'] ?? '';
        if (strlen($name) < 2) $errors[] = 'Admin name too short.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email.';
        if (strlen($pass) < 8) $errors[] = 'Password must be at least 8 characters.';
        if ($pass !== $confirm) $errors[] = 'Passwords do not match.';
        if (empty($errors)) {
            $_SESSION['admin'] = ['name'=>$name,'email'=>$email,'pass'=>$pass];
            header('Location: install.php?step=6'); exit;
        }
    }

    if ($step === 6) {
        try {
            $db   = $_SESSION['db'];
            $app  = $_SESSION['app'];
            $feat = $_SESSION['site_features'] ?? [];
            $adm  = $_SESSION['admin'];

            $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset=utf8mb4";
            $pdo = new PDO($dsn, $db['user'], $db['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
            ]);

            $sqlFile = BASE_PATH . '/database/migrations/001_create_core_tables.sql';
            if (file_exists($sqlFile)) {
                $sql = file_get_contents($sqlFile);
                $statements = array_filter(array_map('trim', preg_split('/;\s*$/m', $sql)), fn($s) => $s !== '' && !preg_match('/^\s*--/', $s));
                $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
                foreach ($statements as $stmt) {
                    if (trim($stmt)) { try { $pdo->exec($stmt); } catch(PDOException $e) {} }
                }
                $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
            }

            $hash = password_hash($adm['pass'], PASSWORD_BCRYPT, ['cost'=>12]);
            $pdo->prepare(
                "INSERT INTO users (name,email,password,role,status,created_at,updated_at)
                 VALUES (:n,:e,:p,'admin','active',NOW(),NOW())
                 ON DUPLICATE KEY UPDATE password=:p2, name=:n2"
            )->execute([':n'=>$adm['name'],':e'=>$adm['email'],':p'=>$hash,':p2'=>$hash,':n2'=>$adm['name']]);

            if ($adm['email'] !== 'admin@netatrack.in') {
                $pdo->prepare("DELETE FROM users WHERE email='admin@netatrack.in' AND name='NetaTrack Admin'")->execute();
            }

            $settings = [
                'site_name'             => $app['name'],
                'site_url'              => $app['url'],
                'site_tagline'          => $app['site_tagline'],
                'meta_description'      => $app['meta_description'],
                'site_logo'             => $app['logo_url'],
                'gemini_api_key'        => $app['gemini_key'],
                'sarvam_api_key'        => $app['sarvam_key'],
                'ai_enabled'            => !empty($app['gemini_key']) ? '1' : '0',
                'google_analytics_id'   => $feat['google_analytics_id'] ?? '',
                'google_adsense_code'   => $feat['google_adsense_code'] ?? '',
                'sponsor_title'         => $feat['sponsor_title'] ?? '',
                'sponsor_html'          => $feat['sponsor_html'] ?? '',
                'announcement_text'     => $feat['announcement_text'] ?? '',
                'announcement_link'     => $feat['announcement_link'] ?? '',
                'announcement_active'   => $feat['announcement_active'] ?? '0',
            ];
            foreach ($settings as $k => $v) {
                $pdo->prepare("INSERT INTO settings (`key`,`value`) VALUES (:k,:v) ON DUPLICATE KEY UPDATE `value`=:v2")
                    ->execute([':k'=>$k,':v'=>$v,':v2'=>$v]);
            }

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
            file_put_contents(BASE_PATH . '/.installed', date('Y-m-d H:i:s') . ' | ' . $app['url']);
            session_destroy();
            $success = $app['url'];
        } catch (Throwable $e) {
            $errors[] = 'Installation failed: ' . $e->getMessage();
        }
    }
}

function checkRequirements(): array {
    return [
        ['PHP >= 8.1', version_compare(PHP_VERSION,'8.1','>=')],
        ['PDO Extension', extension_loaded('pdo')],
        ['PDO MySQL', extension_loaded('pdo_mysql')],
        ['cURL', extension_loaded('curl')],
        ['SimpleXML', extension_loaded('simplexml')],
        ['OpenSSL', extension_loaded('openssl')],
        ['Writable: /', is_writable(BASE_PATH)],
        ['Writable: /public', is_writable(BASE_PATH.'/public')],
    ];
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
    <div class="install-header">
        <div class="brand">🇮🇳 <span>NetaTrack India</span></div>
        <p class="brand-sub">Web Installer</p>
    </div>

    <div class="steps-bar">
        <?php $stepLabels = ['Welcome','Database','Site','Integrations','Admin','Install'];
        for ($i = 1; $i <= 6; $i++): $cls = $i < $step ? 'done' : ($i == $step ? 'active' : ''); ?>
        <div class="step-item <?= $cls ?>"><div class="step-circle"><?= $i < $step ? '✓' : $i ?></div><div class="step-label"><?= $stepLabels[$i-1] ?></div></div>
        <?php if ($i < 6): ?><div class="step-line <?= $i < $step ? 'done' : '' ?>"></div><?php endif; ?>
        <?php endfor; ?>
    </div>

    <div class="install-card">
        <?php if (!empty($errors)): ?><div class="alert alert-error"><?php foreach ($errors as $e): ?><p>❌ <?= htmlspecialchars($e) ?></p><?php endforeach; ?></div><?php endif; ?>
        <?php if ($success): ?>
        <div class="success-screen">
            <div class="success-icon">🎉</div>
            <h2>Installation Complete!</h2>
            <p>Your site, admin account, integrations, and announcements are ready.</p>
            <div class="success-links">
                <a href="<?= htmlspecialchars($success) ?>" class="btn btn-primary">🌐 Visit Site</a>
                <a href="<?= htmlspecialchars($success) ?>/admin/auth/login" class="btn btn-orange">🛡️ Admin Panel</a>
            </div>
            <div class="security-note">⚠️ <strong>Security:</strong> Installer locked. Delete <code>public/install.php</code> for extra safety.</div>
        </div>
        <?php elseif ($step === 1): ?>
        <h2 class="step-title">👋 Welcome to NetaTrack India</h2>
        <p class="step-desc">This wizard sets up your site, database, branding, analytics, ads, sponsor block, announcements, and admin account.</p>
        <div class="req-list">
            <?php $allOk = true; foreach (checkRequirements() as [$label, $ok]): if (!$ok) $allOk = false; ?>
            <div class="req-item"><span class="req-icon"><?= $ok ? '✅' : '❌' ?></span><span class="req-label"><?= htmlspecialchars($label) ?></span><span class="req-status <?= $ok ? 'ok' : 'fail' ?>"><?= $ok ? 'OK' : 'MISSING' ?></span></div>
            <?php endforeach; ?>
        </div>
        <?php if ($allOk): ?><a href="install.php?step=2" class="btn btn-primary btn-full">→ Continue to Database Setup</a><?php else: ?><div class="alert alert-warn">⚠️ Please fix the missing requirements before continuing.</div><?php endif; ?>

        <?php elseif ($step === 2): ?>
        <h2 class="step-title">🗄️ Database Configuration</h2>
        <p class="step-desc">Enter MySQL/MariaDB details. Database will be created automatically if missing.</p>
        <form method="POST" action="install.php?step=2">
            <div class="form-group"><label>Host</label><input type="text" name="db_host" value="<?= htmlspecialchars($_SESSION['db']['host'] ?? '127.0.0.1') ?>"></div>
            <div class="form-group"><label>Port</label><input type="number" name="db_port" value="<?= htmlspecialchars($_SESSION['db']['port'] ?? '3306') ?>"></div>
            <div class="form-group"><label>Database Name</label><input type="text" name="db_name" value="<?= htmlspecialchars($_SESSION['db']['name'] ?? 'netatrack') ?>"></div>
            <div class="form-group"><label>Username</label><input type="text" name="db_user" value="<?= htmlspecialchars($_SESSION['db']['user'] ?? 'root') ?>" autofocus></div>
            <div class="form-group"><label>Password</label><input type="password" name="db_pass"></div>
            <div class="form-actions"><a href="install.php?step=1" class="btn btn-secondary">← Back</a><button type="submit" class="btn btn-primary">Test & Continue →</button></div>
        </form>

        <?php elseif ($step === 3): ?>
        <h2 class="step-title">🌐 Site Branding & AI</h2>
        <p class="step-desc">Set the site name, logo URL, description, timezone, and optional AI API keys.</p>
        <form method="POST" action="install.php?step=3">
            <div class="form-group"><label>Site Name</label><input type="text" name="site_name" value="<?= htmlspecialchars($_SESSION['app']['name'] ?? 'NetaTrack India') ?>"></div>
            <div class="form-group"><label>Site URL</label><input type="url" name="site_url" value="<?= htmlspecialchars($_SESSION['app']['url'] ?? ('http://'.$_SERVER['HTTP_HOST'])) ?>" required></div>
            <div class="form-group"><label>Tagline</label><input type="text" name="site_tagline" value="<?= htmlspecialchars($_SESSION['app']['site_tagline'] ?? '') ?>" placeholder="Track promises. Expose corruption."></div>
            <div class="form-group"><label>Meta Description</label><input type="text" name="meta_description" value="<?= htmlspecialchars($_SESSION['app']['meta_description'] ?? '') ?>" placeholder="Political accountability platform for India"></div>
            <div class="form-group"><label>Logo URL</label><input type="url" name="logo_url" value="<?= htmlspecialchars($_SESSION['app']['logo_url'] ?? '') ?>" placeholder="https://yourdomain.com/logo.png"></div>
            <div class="form-group"><label>Timezone</label><select name="timezone"><?php foreach (['Asia/Kolkata','UTC','Asia/Dubai','Asia/Singapore','Europe/London','America/New_York'] as $z): ?><option value="<?= $z ?>" <?= ($_SESSION['app']['timezone'] ?? 'Asia/Kolkata') === $z ? 'selected' : '' ?>><?= $z ?></option><?php endforeach; ?></select></div>
            <hr class="divider">
            <p class="optional-label">🤖 AI Keys <span class="hint">(optional)</span></p>
            <div class="form-group"><label>Gemini API Key</label><input type="password" name="gemini_key" value="<?= htmlspecialchars($_SESSION['app']['gemini_key'] ?? '') ?>"></div>
            <div class="form-group"><label>Sarvam AI Key</label><input type="password" name="sarvam_key" value="<?= htmlspecialchars($_SESSION['app']['sarvam_key'] ?? '') ?>"></div>
            <div class="form-actions"><a href="install.php?step=2" class="btn btn-secondary">← Back</a><button type="submit" class="btn btn-primary">Continue →</button></div>
        </form>

        <?php elseif ($step === 4): ?>
        <h2 class="step-title">📢 Integrations & Widgets</h2>
        <p class="step-desc">You can configure analytics, ads, sponsor block, and homepage announcements here. These can also be changed later from admin settings.</p>
        <form method="POST" action="install.php?step=4">
            <div class="form-group"><label>Google Analytics ID</label><input type="text" name="google_analytics_id" value="<?= htmlspecialchars($_SESSION['site_features']['google_analytics_id'] ?? '') ?>" placeholder="G-XXXXXXXXXX"></div>
            <div class="form-group"><label>Google AdSense Code / Publisher ID</label><input type="text" name="google_adsense_code" value="<?= htmlspecialchars($_SESSION['site_features']['google_adsense_code'] ?? '') ?>" placeholder="ca-pub-xxxxxxxxxxxxxxxx"></div>
            <div class="form-group"><label>Sponsor Title</label><input type="text" name="sponsor_title" value="<?= htmlspecialchars($_SESSION['site_features']['sponsor_title'] ?? '') ?>" placeholder="Sponsored By"></div>
            <div class="form-group"><label>Sponsor HTML / Embed</label><input type="text" name="sponsor_html" value="<?= htmlspecialchars($_SESSION['site_features']['sponsor_html'] ?? '') ?>" placeholder="<a href='...'><img ...></a>"></div>
            <div class="form-group"><label>Announcement Text</label><input type="text" name="announcement_text" value="<?= htmlspecialchars($_SESSION['site_features']['announcement_text'] ?? '') ?>" placeholder="Breaking: New report published on state leaders"></div>
            <div class="form-group"><label>Announcement Link</label><input type="url" name="announcement_link" value="<?= htmlspecialchars($_SESSION['site_features']['announcement_link'] ?? '') ?>" placeholder="https://yourdomain.com/reports"></div>
            <div class="form-group checkbox-row"><label><input type="checkbox" name="announcement_active" value="1" <?= !empty($_SESSION['site_features']['announcement_active']) ? 'checked' : '' ?>> Enable Announcement Bar</label></div>
            <div class="form-actions"><a href="install.php?step=3" class="btn btn-secondary">← Back</a><button type="submit" class="btn btn-primary">Continue →</button></div>
        </form>

        <?php elseif ($step === 5): ?>
        <h2 class="step-title">👤 Create Admin Account</h2>
        <p class="step-desc">This will be your primary administrator account.</p>
        <form method="POST" action="install.php?step=5">
            <div class="form-group"><label>Full Name</label><input type="text" name="admin_name" value="<?= htmlspecialchars($_SESSION['admin']['name'] ?? '') ?>" required autofocus></div>
            <div class="form-group"><label>Email Address</label><input type="email" name="admin_email" value="<?= htmlspecialchars($_SESSION['admin']['email'] ?? '') ?>" required></div>
            <div class="form-group"><label>Password</label><input type="password" name="admin_pass" required></div>
            <div class="form-group"><label>Confirm Password</label><input type="password" name="admin_pass2" required></div>
            <div class="form-actions"><a href="install.php?step=4" class="btn btn-secondary">← Back</a><button type="submit" class="btn btn-primary">Continue →</button></div>
        </form>

        <?php elseif ($step === 6): ?>
        <h2 class="step-title">🚀 Ready to Install</h2>
        <p class="step-desc">Review your config and finish installation.</p>
        <div class="review-grid">
            <div class="review-section"><h4>🗄️ Database</h4><div class="review-row"><span>Host</span><strong><?= htmlspecialchars($_SESSION['db']['host'] ?? '') ?></strong></div><div class="review-row"><span>Database</span><strong><?= htmlspecialchars($_SESSION['db']['name'] ?? '') ?></strong></div><div class="review-row"><span>User</span><strong><?= htmlspecialchars($_SESSION['db']['user'] ?? '') ?></strong></div></div>
            <div class="review-section"><h4>🌐 Site</h4><div class="review-row"><span>Name</span><strong><?= htmlspecialchars($_SESSION['app']['name'] ?? '') ?></strong></div><div class="review-row"><span>Logo</span><strong><?= !empty($_SESSION['app']['logo_url']) ? 'Set' : 'Not set' ?></strong></div><div class="review-row"><span>Analytics</span><strong><?= !empty($_SESSION['site_features']['google_analytics_id']) ? 'Enabled' : 'Off' ?></strong></div></div>
            <div class="review-section"><h4>👤 Admin</h4><div class="review-row"><span>Name</span><strong><?= htmlspecialchars($_SESSION['admin']['name'] ?? '') ?></strong></div><div class="review-row"><span>Email</span><strong><?= htmlspecialchars($_SESSION['admin']['email'] ?? '') ?></strong></div><div class="review-row"><span>Announcement</span><strong><?= !empty($_SESSION['site_features']['announcement_active']) ? 'Active' : 'Off' ?></strong></div></div>
        </div>
        <form method="POST" action="install.php?step=6"><div class="form-actions"><a href="install.php?step=5" class="btn btn-secondary">← Back</a><button type="submit" class="btn btn-install">🚀 Install NetaTrack India</button></div></form>
        <?php endif; ?>
    </div>
    <p class="install-footer">NetaTrack India — Political Accountability Platform for India</p>
</div>
</body>
</html>
