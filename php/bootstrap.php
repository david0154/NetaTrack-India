<?php
/**
 * NetaTrack India — Bootstrap
 * Loaded by index.php before routing.
 */

define('BASE_PATH', __DIR__);
define('START_TIME', microtime(true));

// ---- Autoloader ----
if (file_exists(__DIR__.'/vendor/autoload.php')) {
    require __DIR__.'/vendor/autoload.php';
} else {
    // Fallback PSR-4 autoloader
    spl_autoload_register(function(string $class) {
        $map = [
            'NetaTrack\\' => __DIR__.'/src/',
        ];
        foreach ($map as $prefix => $base) {
            if (!str_starts_with($class, $prefix)) continue;
            $rel  = str_replace('\\', '/', substr($class, strlen($prefix)));
            $file = $base . $rel . '.php';
            if (file_exists($file)) { require $file; return; }
        }
    });
}

// ---- Environment ----
if (file_exists(__DIR__.'/.env')) {
    foreach (file(__DIR__.'/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        [$k, $v] = array_pad(explode('=', $line, 2), 2, '');
        $k = trim($k); $v = trim($v, '"\' ');
        if (!getenv($k)) { putenv("$k=$v"); $_ENV[$k] = $v; }
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $val = $_ENV[$key] ?? getenv($key);
        return ($val !== false && $val !== '') ? $val : $default;
    }
}

// ---- Load helpers ----
require __DIR__.'/src/Core/helpers.php';

// ---- Session ----
if (session_status() === PHP_SESSION_NONE) {
    $secure   = env('SESSION_SECURE', 'true') === 'true';
    $lifetime = (int)env('SESSION_LIFETIME', 120) * 60;
    session_set_cookie_params([
        'lifetime' => $lifetime,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_name('netatrack_sess');
    session_start();
}

// ---- Timezone ----
date_default_timezone_set(env('APP_TIMEZONE', 'Asia/Kolkata'));

// ---- Error handling ----
$debug = env('APP_DEBUG', 'false') === 'true';
ini_set('display_errors', $debug ? '1' : '0');
error_reporting($debug ? E_ALL : E_ERROR | E_PARSE);

if (!$debug) {
    set_exception_handler(function(\Throwable $e) {
        error_log('[NetaTrack] Exception: '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine());
        http_response_code(500);
        require BASE_PATH.'/views/errors/500.php';
        exit;
    });
}

// ---- Load routes ----
require __DIR__.'/routes/web.php';
