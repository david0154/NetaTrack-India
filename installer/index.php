<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $env = "APP_ENV=production\nAPP_URL=" . $_POST['app_url'] . "\n" .
        "DB_HOST=" . $_POST['db_host'] . "\nDB_PORT=" . $_POST['db_port'] . "\nDB_NAME=" . $_POST['db_name'] . "\nDB_USER=" . $_POST['db_user'] . "\nDB_PASS=" . $_POST['db_pass'] . "\n" .
        "AWS_REGION=" . $_POST['aws_region'] . "\nAWS_ACCESS_KEY_ID=" . $_POST['aws_key'] . "\nAWS_SECRET_ACCESS_KEY=" . $_POST['aws_secret'] . "\nAWS_AI_ENDPOINT=" . $_POST['aws_endpoint'] . "\nAWS_AI_MODEL_ID=" . $_POST['aws_model'] . "\nAWS_AI_ENABLED=true\n";
    file_put_contents(__DIR__ . '/../.env', $env);

    require_once __DIR__ . '/../src/Database.php';
    try {
        $pdo = Database::connection();
        $sql = file_get_contents(__DIR__ . '/schema.sql');
        $pdo->exec($sql);
        $passwordHash = password_hash($_POST['admin_password'], PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('INSERT INTO users (name,email,password_hash,role) VALUES (?,?,?,?)');
        $stmt->execute([$_POST['admin_name'], $_POST['admin_email'], $passwordHash, 'super_admin']);
        $success = true;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!doctype html><html><body style="font-family:Arial;max-width:700px;margin:30px auto;">
<h1>NetaTrack One-Click Installer</h1>
<?php if (!empty($success)): ?><p style="color:green;">Installed successfully.</p><?php endif; ?>
<?php if (!empty($error)): ?><p style="color:red;"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post">
<h3>App</h3><input name="app_url" placeholder="APP URL" required style="width:100%;margin:5px 0;"/>
<h3>Database</h3><input name="db_host" placeholder="DB Host" required style="width:100%;margin:5px 0;"/><input name="db_port" value="3306" required style="width:100%;margin:5px 0;"/><input name="db_name" placeholder="DB Name" required style="width:100%;margin:5px 0;"/><input name="db_user" placeholder="DB User" required style="width:100%;margin:5px 0;"/><input name="db_pass" placeholder="DB Password" style="width:100%;margin:5px 0;"/>
<h3>AWS AI</h3><input name="aws_region" placeholder="AWS Region" style="width:100%;margin:5px 0;"/><input name="aws_key" placeholder="AWS Access Key" style="width:100%;margin:5px 0;"/><input name="aws_secret" placeholder="AWS Secret" style="width:100%;margin:5px 0;"/><input name="aws_endpoint" placeholder="AWS AI Endpoint" style="width:100%;margin:5px 0;"/><input name="aws_model" placeholder="AWS Model ID" style="width:100%;margin:5px 0;"/>
<h3>Admin</h3><input name="admin_name" placeholder="Admin Name" required style="width:100%;margin:5px 0;"/><input name="admin_email" placeholder="Admin Email" required style="width:100%;margin:5px 0;"/><input type="password" name="admin_password" placeholder="Admin Password" required style="width:100%;margin:5px 0;"/>
<button type="submit">Install Now</button>
</form>
</body></html>
