<?php
/**
 * NetaTrack India - Route Definitions
 */
return [
    // Public Routes
    'GET /'                          => ['PublicController', 'home'],
    'GET /leaders'                   => ['PublicController', 'leaders'],
    'GET /leaders/{id}'              => ['PublicController', 'leaderDetail'],
    'GET /states'                    => ['PublicController', 'states'],
    'GET /states/{slug}'             => ['PublicController', 'stateDetail'],
    'GET /promises'                  => ['PublicController', 'promises'],
    'GET /projects'                  => ['PublicController', 'projects'],
    'GET /corruption'                => ['PublicController', 'corruption'],
    'GET /submit'                    => ['PublicController', 'submitForm'],
    'POST /submit'                   => ['PublicController', 'submitReport'],
    'GET /search'                    => ['PublicController', 'search'],
    'GET /analytics'                 => ['PublicController', 'analytics'],

    // Auth Routes
    'GET /login'                     => ['AuthController', 'loginForm'],
    'POST /login'                    => ['AuthController', 'login'],
    'GET /register'                  => ['AuthController', 'registerForm'],
    'POST /register'                 => ['AuthController', 'register'],
    'GET /logout'                    => ['AuthController', 'logout'],

    // Admin Routes
    'GET /admin'                     => ['Admin\DashboardController', 'index'],
    'GET /admin/leaders'             => ['Admin\LeaderController', 'index'],
    'GET /admin/leaders/create'      => ['Admin\LeaderController', 'create'],
    'POST /admin/leaders/create'     => ['Admin\LeaderController', 'store'],
    'GET /admin/leaders/{id}/edit'   => ['Admin\LeaderController', 'edit'],
    'POST /admin/leaders/{id}/edit'  => ['Admin\LeaderController', 'update'],
    'POST /admin/leaders/{id}/delete'=> ['Admin\LeaderController', 'delete'],
    'GET /admin/promises'            => ['Admin\PromiseController', 'index'],
    'POST /admin/promises/create'    => ['Admin\PromiseController', 'store'],
    'GET /admin/projects'            => ['Admin\ProjectController', 'index'],
    'POST /admin/projects/create'    => ['Admin\ProjectController', 'store'],
    'GET /admin/reports'             => ['Admin\ReportController', 'index'],
    'POST /admin/reports/{id}/approve'=> ['Admin\ReportController', 'approve'],
    'POST /admin/reports/{id}/reject' => ['Admin\ReportController', 'reject'],
    'GET /admin/scraper'             => ['Admin\ScraperController', 'index'],
    'POST /admin/scraper/start'      => ['Admin\ScraperController', 'start'],
    'GET /admin/settings'            => ['Admin\SettingsController', 'index'],
    'POST /admin/settings'           => ['Admin\SettingsController', 'update'],
    'GET /admin/users'               => ['Admin\UserController', 'index'],
    'GET /admin/analytics'           => ['Admin\AnalyticsController', 'index'],

    // API Routes
    'GET /api/leaders'               => ['Api\LeaderController', 'index'],
    'GET /api/leaders/{id}'          => ['Api\LeaderController', 'show'],
    'GET /api/states'                => ['Api\StateController', 'index'],
    'GET /api/promises'              => ['Api\PromiseController', 'index'],
    'GET /api/projects'              => ['Api\ProjectController', 'index'],
    'GET /api/stats'                 => ['Api\StatsController', 'index'],
    'POST /api/submit'               => ['Api\SubmitController', 'store'],
];
