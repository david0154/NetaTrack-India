<?php
/**
 * NetaTrack India — Route Definitions
 * Registered via the Router core class.
 */

use NetaTrack\Core\Router;

$router = Router::getInstance();

/* =====================================================================
   PUBLIC ROUTES
   ===================================================================== */

$router->get('',                         'PublicController@home');
$router->get('leaders',                  'PublicController@leaders');
$router->get('leaders/{slug}',           'PublicController@leaderProfile');
$router->get('promises',                 'PublicController@promises');
$router->get('projects',                 'PublicController@projects');
$router->get('corruption',               'PublicController@corruption');

$router->get('report',                   'ReportController@showForm');
$router->post('report',                  'ReportController@submit');

/* =====================================================================
   AUTH ROUTES
   ===================================================================== */

$router->get('auth/login',               'AuthController@loginForm');
$router->post('auth/login',              'AuthController@login');
$router->get('auth/register',            'AuthController@registerForm');
$router->post('auth/register',           'AuthController@register');
$router->get('auth/logout',              'AuthController@logout');

$router->get('admin/auth/login',         'AuthController@adminLoginForm');
$router->post('admin/auth/login',        'AuthController@adminLogin');

/* =====================================================================
   ADMIN ROUTES  (middleware: auth + admin role)
   ===================================================================== */

$router->group('admin', ['middleware' => ['auth', 'admin']], function(Router $r) {

    // Dashboard
    $r->get('',                          'AdminController@dashboard');

    // Leaders
    $r->get('leaders',                   'AdminController@leadersList');
    $r->get('leaders/create',            'AdminController@leaderCreate');
    $r->post('leaders/create',           'AdminController@leaderStore');
    $r->get('leaders/{id}/edit',         'AdminController@leaderEdit');
    $r->post('leaders/{id}/update',      'AdminController@leaderUpdate');
    $r->post('leaders/{id}/delete',      'AdminController@leaderDelete');

    // Reports
    $r->get('reports',                   'AdminController@reportsList');
    $r->post('reports/{id}/approve',     'AdminController@reportApprove');
    $r->post('reports/{id}/reject',      'AdminController@reportReject');

    // Projects
    $r->get('projects',                  'AdminController@projectsList');

    // Users
    $r->get('users',                     'AdminController@usersList');
    $r->post('users/{id}/ban',           'AdminController@userBan');

    // Settings
    $r->get('settings',                  'AdminController@settings');
    $r->post('settings',                 'AdminController@settingsSave');

    // Analytics
    $r->get('analytics',                 'AdminController@analytics');

    // Scraper
    $r->get('scraper',                   'AdminController@scraper');
    $r->post('scraper/run',              'AdminController@scraperRun');
});

/* =====================================================================
   API ROUTES (JSON)
   ===================================================================== */

$router->group('api/v1', ['middleware' => []], function(Router $r) {
    $r->get('leaders',                   'Api\LeaderApiController@index');
    $r->get('leaders/{slug}',            'Api\LeaderApiController@show');
    $r->get('promises',                  'Api\PromiseApiController@index');
    $r->get('projects',                  'Api\ProjectApiController@index');
    $r->get('stats',                     'Api\StatsApiController@index');
});

/* =====================================================================
   FALLBACK
   ===================================================================== */
$router->fallback(function() {
    http_response_code(404);
    require __DIR__.'/../views/errors/404.php';
});
