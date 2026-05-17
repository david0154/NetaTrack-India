<?php
/**
 * NetaTrack India - Front Controller
 * All requests are routed through here via .htaccess
 */

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

// Load routes
require ROOT_PATH . '/routes/web.php';
require ROOT_PATH . '/routes/api.php';

// Dispatch
NetaTrack\Core\Router::dispatch();
