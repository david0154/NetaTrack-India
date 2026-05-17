<?php
/**
 * NetaTrack India - Application Bootstrap
 * Initializes autoloading, constants, and core services
 */

declare(strict_types=1);

define('ROOT_PATH', __DIR__);
define('VIEWS_PATH', ROOT_PATH . '/views');
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('UPLOAD_PATH', STORAGE_PATH . '/uploads');
define('APP_START', microtime(true));

// Load .env
if (file_exists(ROOT_PATH . '/.env')) {
    $lines = file(ROOT_PATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (str_contains($line, '=')) {
            [$key, $val] = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($val));
            $_ENV[trim($key)] = trim($val);
        }
    }
}

define('APP_DEBUG', (bool)(getenv('APP_DEBUG') ?: false));

// Error handling
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
    set_error_handler(function($errno, $errstr, $errfile, $errline) {
        error_log("[$errno] $errstr in $errfile:$errline");
    });
    set_exception_handler(function(\Throwable $e) {
        error_log('[EXCEPTION] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
        http_response_code(500);
        if (str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Internal server error']);
        } else {
            require ROOT_PATH . '/views/errors/500.php';
        }
        exit;
    });
}

// Autoloader
if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    require ROOT_PATH . '/vendor/autoload.php';
} else {
    // Simple PSR-4 autoloader fallback
    spl_autoload_register(function(string $class) {
        $class = str_replace('NetaTrack\\', '', $class);
        $path = ROOT_PATH . '/src/' . str_replace('\\', '/', $class) . '.php';
        if (file_exists($path)) require_once $path;
    });
}

require ROOT_PATH . '/src/Helpers/Helpers.php';

// Initialize Auth & Session
$auth = \NetaTrack\Core\Auth::getInstance();
$auth->init();

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Create required directories
foreach ([STORAGE_PATH, UPLOAD_PATH, STORAGE_PATH.'/cache', STORAGE_PATH.'/logs'] as $dir) {
    if (!is_dir($dir)) mkdir($dir, 0755, true);
}
