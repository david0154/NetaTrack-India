<?php
/**
 * NetaTrack India - Application Bootstrap
 * Loads config, registers autoloader, starts session, sets error handling
 */

defined('BASE_PATH') || define('BASE_PATH', dirname(__DIR__));
defined('SRC_PATH')  || define('SRC_PATH',  __DIR__);
defined('APP_ENV')   || define('APP_ENV', getenv('APP_ENV') ?: 'production');

// Error reporting
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// Autoloader for src/ classes
spl_autoload_register(function (string $class): void {
    $file = SRC_PATH . '/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Load .env
$envFile = BASE_PATH . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (!str_contains($line, '=')) continue;
        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Validate required env variables
$required = [
    'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS',
    'JWT_SECRET', 'APP_URL',
];
foreach ($required as $var) {
    if (empty($_ENV[$var]) && empty(getenv($var))) {
        http_response_code(500);
        die(json_encode(['error' => "Missing required environment variable: $var"]));
    }
}

// Secure session
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400 * 30,
        'path'     => '/',
        'secure'   => APP_ENV === 'production',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Timezone
date_default_timezone_set('Asia/Kolkata');

// CORS headers (for API routes)
if (php_sapi_name() !== 'cli') {
    $allowedOrigins = explode(',', getenv('ALLOWED_ORIGINS') ?: getenv('APP_URL') ?: '*');
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if (in_array($origin, $allowedOrigins, true) || in_array('*', $allowedOrigins, true)) {
        header('Access-Control-Allow-Origin: ' . ($origin ?: '*'));
    }
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-Token');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}
