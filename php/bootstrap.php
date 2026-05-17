<?php
/**
 * Bootstrap - Loads all core dependencies
 */

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400 * 7,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'NetaTrack\\';
    $base_dir = APP_PATH . '/src/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) require $file;
});

// Load config
$config = require APP_PATH . '/config/app.php';
define('APP_CONFIG', $config);

// Timezone
date_default_timezone_set($config['timezone'] ?? 'Asia/Kolkata');

// Load environment
\NetaTrack\Core\Env::load(ROOT_PATH . '/.env');

// Connect DB
\NetaTrack\Core\Database::getInstance();
