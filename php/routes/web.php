<?php
/**
 * NetaTrack India - Web Routes
 */

use NetaTrack\Core\Router;

$router = new Router();

// ============================================================
// PUBLIC ROUTES
// ============================================================
$router->get('/', 'HomeController@index');
$router->get('/leaders', 'LeaderController@index');
$router->get('/leader/{uuid}', 'LeaderController@show');
$router->get('/states', 'StateController@index');
$router->get('/state/{code}', 'StateController@show');
$router->get('/promises', 'PromiseController@index');
$router->get('/promise/{uuid}', 'PromiseController@show');
$router->get('/projects', 'ProjectController@index');
$router->get('/project/{uuid}', 'ProjectController@show');
$router->get('/corruption', 'CorruptionController@index');
$router->get('/fake-claims', 'PromiseController@fakeClaims');
$router->get('/reports', 'ReportController@publicIndex');
$router->get('/report/{uuid}', 'ReportController@publicShow');
$router->get('/search', 'SearchController@index');

// AUTH ROUTES
$router->get('/login', 'AuthController@loginForm');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@registerForm');
$router->post('/register', 'AuthController@register');
$router->get('/logout', 'AuthController@logout');
$router->get('/forgot-password', 'AuthController@forgotPasswordForm');
$router->post('/forgot-password', 'AuthController@forgotPassword');
$router->get('/reset-password/{token}', 'AuthController@resetPasswordForm');
$router->post('/reset-password', 'AuthController@resetPassword');

// USER ACCOUNT ROUTES (requires auth)
$router->get('/my-account', 'UserController@dashboard', ['NetaTrack\Middleware\AuthMiddleware']);
$router->get('/my-reports', 'UserController@myReports', ['NetaTrack\Middleware\AuthMiddleware']);
$router->get('/submit-report', 'ReportController@submitForm', ['NetaTrack\Middleware\AuthMiddleware']);
$router->post('/submit-report', 'ReportController@submit', ['NetaTrack\Middleware\AuthMiddleware']);

// ============================================================
// ADMIN ROUTES
// ============================================================
$router->group('/admin', function(Router $r) {
    $r->get('', 'Admin\DashboardController@index');
    $r->get('/login', 'Admin\AuthController@loginForm');
    $r->post('/login', 'Admin\AuthController@login');
    $r->get('/logout', 'Admin\AuthController@logout');

    // Leaders
    $r->get('/leaders', 'Admin\LeaderController@index');
    $r->get('/leaders/create', 'Admin\LeaderController@create');
    $r->post('/leaders/create', 'Admin\LeaderController@store');
    $r->get('/leaders/{id}/edit', 'Admin\LeaderController@edit');
    $r->post('/leaders/{id}/edit', 'Admin\LeaderController@update');
    $r->delete('/leaders/{id}', 'Admin\LeaderController@destroy');

    // Promises
    $r->get('/promises', 'Admin\PromiseController@index');
    $r->post('/promises', 'Admin\PromiseController@store');
    $r->post('/promises/{id}/update', 'Admin\PromiseController@update');
    $r->delete('/promises/{id}', 'Admin\PromiseController@destroy');

    // Projects
    $r->get('/projects', 'Admin\ProjectController@index');
    $r->post('/projects', 'Admin\ProjectController@store');
    $r->post('/projects/{id}/update', 'Admin\ProjectController@update');
    $r->delete('/projects/{id}', 'Admin\ProjectController@destroy');

    // Reports
    $r->get('/reports', 'Admin\ReportController@index');
    $r->post('/reports/{id}/approve', 'Admin\ReportController@approve');
    $r->post('/reports/{id}/reject', 'Admin\ReportController@reject');
    $r->post('/reports/{id}/merge', 'Admin\ReportController@merge');

    // Scraper
    $r->get('/scraper', 'Admin\ScraperController@index');
    $r->post('/scraper/jobs', 'Admin\ScraperController@createJob');
    $r->post('/scraper/jobs/{id}/toggle', 'Admin\ScraperController@toggleJob');
    $r->post('/scraper/jobs/{id}/retry', 'Admin\ScraperController@retryJob');
    $r->get('/scraper/queue', 'Admin\ScraperController@queue');
    $r->post('/scraper/queue/{id}/approve', 'Admin\ScraperController@approveItem');
    $r->post('/scraper/queue/{id}/reject', 'Admin\ScraperController@rejectItem');

    // Settings
    $r->get('/settings', 'Admin\SettingsController@index');
    $r->post('/settings', 'Admin\SettingsController@update');
    $r->get('/settings/smtp', 'Admin\SettingsController@smtp');
    $r->post('/settings/smtp', 'Admin\SettingsController@updateSmtp');
    $r->get('/settings/ads', 'Admin\SettingsController@ads');
    $r->post('/settings/ads', 'Admin\SettingsController@updateAds');
    $r->get('/settings/seo', 'Admin\SettingsController@seo');
    $r->post('/settings/seo', 'Admin\SettingsController@updateSeo');

    // Users
    $r->get('/users', 'Admin\UserController@index');
    $r->post('/users/{id}/ban', 'Admin\UserController@ban');
    $r->post('/users/{id}/unban', 'Admin\UserController@unban');

    // States & Parties
    $r->get('/states', 'Admin\StateController@index');
    $r->post('/states', 'Admin\StateController@store');
    $r->post('/states/{id}/update', 'Admin\StateController@update');
    $r->get('/parties', 'Admin\PartyController@index');
    $r->post('/parties', 'Admin\PartyController@store');
    $r->post('/parties/{id}/update', 'Admin\PartyController@update');

}, ['NetaTrack\Middleware\AdminMiddleware']);

return $router;
