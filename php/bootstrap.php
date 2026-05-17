<?php
/**
 * Bootstrap - Loads all core dependencies
 */

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400 * 7,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

spl_autoload_register(function ($class) {
    $prefix = 'NetaTrack\\';
    $base_dir = APP_PATH . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

$config = require APP_PATH . '/config/app.php';
define('APP_CONFIG', $config);

date_default_timezone_set($config['timezone'] ?? 'Asia/Kolkata');

\NetaTrack\Core\Env::load(ROOT_PATH . '/.env');

try {
    \NetaTrack\Core\Database::getInstance();
} catch (\Throwable $e) {
    error_log('Bootstrap DB init failed: ' . $e->getMessage());
}
