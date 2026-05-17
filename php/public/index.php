<?php
/**
 * NetaTrack India — Front Controller
 * Document root MUST point to: php/public/
 * All requests route through here.
 */

declare(strict_types=1);

// One level up from public/ is the app root
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);

require_once ROOT_PATH . '/bootstrap.php';

// Load routes
$routesWeb = ROOT_PATH . '/routes/web.php';
$routesApi = ROOT_PATH . '/routes/api.php';

if (file_exists($routesWeb)) require $routesWeb;
if (file_exists($routesApi)) require $routesApi;

// Dispatch — fallback to simple home if Router not set up yet
if (class_exists('NetaTrack\\Core\\Router')) {
    NetaTrack\Core\Router::dispatch();
} else {
    // Minimal fallback: show home or installer redirect
    $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

    if ($uri === '' || $uri === 'index.php') {
        require ROOT_PATH . '/views/home.php';
    } elseif ($uri === 'install' || $uri === 'install.php') {
        require __DIR__ . '/install.php';
    } else {
        http_response_code(404);
        require ROOT_PATH . '/views/404.php';
    }
}
