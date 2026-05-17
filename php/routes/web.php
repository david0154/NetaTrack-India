<?php
/**
 * NetaTrack India - Web Routes
 */

use NetaTrack\Core\Router;

// ─── Public Routes ─────────────────────────────────────────────────────────
Router::get('/', [\NetaTrack\Controllers\HomeController::class, 'index'], 'home');
Router::get('/leaders', [\NetaTrack\Controllers\LeaderController::class, 'index'], 'leaders.index');
Router::get('/leaders/{slug}', [\NetaTrack\Controllers\LeaderController::class, 'show'], 'leaders.show');
Router::get('/projects', [\NetaTrack\Controllers\ProjectController::class, 'index'], 'projects.index');
Router::get('/projects/{id}', [\NetaTrack\Controllers\ProjectController::class, 'show'], 'projects.show');
Router::get('/promises', [\NetaTrack\Controllers\PromiseController::class, 'index'], 'promises.index');
Router::get('/states', [\NetaTrack\Controllers\StateController::class, 'index'], 'states.index');
Router::get('/states/{slug}', [\NetaTrack\Controllers\StateController::class, 'show'], 'states.show');
Router::get('/corruption', [\NetaTrack\Controllers\CorruptionController::class, 'index'], 'corruption.index');
Router::get('/submit-report', [\NetaTrack\Controllers\ReportController::class, 'create'], 'report.create');
Router::post('/submit-report', [\NetaTrack\Controllers\ReportController::class, 'store'], 'report.store');
Router::get('/search', [\NetaTrack\Controllers\SearchController::class, 'index'], 'search');

// ─── Auth Routes ────────────────────────────────────────────────────────────
Router::group(['prefix' => 'auth', 'name' => 'auth.'], function () {
    Router::get('/login',    [\NetaTrack\Controllers\Auth\LoginController::class, 'showLogin'],    'login');
    Router::post('/login',   [\NetaTrack\Controllers\Auth\LoginController::class, 'login'],        'login.post');
    Router::get('/register', [\NetaTrack\Controllers\Auth\RegisterController::class, 'showForm'], 'register');
    Router::post('/register',[\NetaTrack\Controllers\Auth\RegisterController::class, 'register'],  'register.post');
    Router::get('/logout',   [\NetaTrack\Controllers\Auth\LoginController::class, 'logout'],       'logout');
    Router::get('/forgot-password', [\NetaTrack\Controllers\Auth\ForgotPasswordController::class, 'show'], 'forgot');
    Router::post('/forgot-password',[\NetaTrack\Controllers\Auth\ForgotPasswordController::class, 'send'], 'forgot.post');
});

// ─── Admin Routes ────────────────────────────────────────────────────────────
Router::group(['prefix' => 'admin', 'name' => 'admin.', 'middleware' => ['AdminMiddleware']], function () {
    Router::get('/',                 [\NetaTrack\Controllers\Admin\DashboardController::class, 'index'],    'dashboard');
    Router::get('/leaders',          [\NetaTrack\Controllers\Admin\LeaderAdminController::class, 'index'], 'leaders');
    Router::get('/leaders/create',   [\NetaTrack\Controllers\Admin\LeaderAdminController::class, 'create'],'leaders.create');
    Router::post('/leaders',         [\NetaTrack\Controllers\Admin\LeaderAdminController::class, 'store'],  'leaders.store');
    Router::get('/leaders/{id}/edit',[\NetaTrack\Controllers\Admin\LeaderAdminController::class, 'edit'],  'leaders.edit');
    Router::post('/leaders/{id}',    [\NetaTrack\Controllers\Admin\LeaderAdminController::class, 'update'],'leaders.update');
    Router::post('/leaders/{id}/delete',[\NetaTrack\Controllers\Admin\LeaderAdminController::class, 'delete'],'leaders.delete');

    Router::get('/projects',         [\NetaTrack\Controllers\Admin\ProjectAdminController::class, 'index'], 'projects');
    Router::get('/projects/create',  [\NetaTrack\Controllers\Admin\ProjectAdminController::class, 'create'],'projects.create');
    Router::post('/projects',        [\NetaTrack\Controllers\Admin\ProjectAdminController::class, 'store'], 'projects.store');
    Router::get('/projects/{id}/edit',[\NetaTrack\Controllers\Admin\ProjectAdminController::class, 'edit'],'projects.edit');
    Router::post('/projects/{id}',   [\NetaTrack\Controllers\Admin\ProjectAdminController::class, 'update'],'projects.update');

    Router::get('/promises',         [\NetaTrack\Controllers\Admin\PromiseAdminController::class, 'index'], 'promises');
    Router::get('/promises/create',  [\NetaTrack\Controllers\Admin\PromiseAdminController::class, 'create'],'promises.create');
    Router::post('/promises',        [\NetaTrack\Controllers\Admin\PromiseAdminController::class, 'store'], 'promises.store');

    Router::get('/reports',          [\NetaTrack\Controllers\Admin\ReportAdminController::class, 'index'],  'reports');
    Router::post('/reports/{id}/approve',[\NetaTrack\Controllers\Admin\ReportAdminController::class, 'approve'],'reports.approve');
    Router::post('/reports/{id}/reject', [\NetaTrack\Controllers\Admin\ReportAdminController::class, 'reject'], 'reports.reject');

    Router::get('/users',            [\NetaTrack\Controllers\Admin\UserAdminController::class, 'index'],    'users');
    Router::post('/users/{id}/ban',  [\NetaTrack\Controllers\Admin\UserAdminController::class, 'ban'],      'users.ban');

    Router::get('/settings',         [\NetaTrack\Controllers\Admin\SettingsController::class, 'index'],     'settings');
    Router::post('/settings',        [\NetaTrack\Controllers\Admin\SettingsController::class, 'update'],    'settings.update');

    Router::get('/analytics',        [\NetaTrack\Controllers\Admin\AnalyticsController::class, 'index'],    'analytics');
    Router::get('/scraper',          [\NetaTrack\Controllers\Admin\ScraperController::class, 'index'],      'scraper');
});

// Admin Login (outside middleware)
Router::get('/admin/login',  [\NetaTrack\Controllers\Auth\AdminLoginController::class, 'show'],  'admin.login');
Router::post('/admin/login', [\NetaTrack\Controllers\Auth\AdminLoginController::class, 'login'], 'admin.login.post');
Router::get('/admin/logout', [\NetaTrack\Controllers\Auth\AdminLoginController::class, 'logout'],'admin.logout');
