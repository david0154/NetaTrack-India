<?php
/**
 * NetaTrack India - Front Controller
 */

require_once __DIR__ . '/bootstrap.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Route to API or Web
if (str_starts_with($uri, '/api/')) {
    $router = require __DIR__ . '/routes/api.php';
} else {
    $router = require __DIR__ . '/routes/web.php';
}

$router->dispatch();
