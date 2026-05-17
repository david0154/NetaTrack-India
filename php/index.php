<?php
/**
 * NetaTrack India - Main Entry Point
 * Phase 1: Core Backend
 */

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/php');
define('VERSION', '1.0.0');
define('APP_NAME', 'NetaTrack India');

// Load bootstrap
require_once APP_PATH . '/bootstrap.php';

// Start router
$router = new \NetaTrack\Core\Router();
$router->dispatch();
