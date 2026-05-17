<?php
/**
 * NetaTrack India - API Routes
 */

use NetaTrack\Core\Router;

$router = new Router();

$router->group('/api/v1', function(Router $r) {
    // Public API
    $r->get('/stats', 'Api\StatsController@index');
    $r->get('/leaders', 'Api\LeaderController@index');
    $r->get('/leaders/{uuid}', 'Api\LeaderController@show');
    $r->get('/leaders/{uuid}/promises', 'Api\LeaderController@promises');
    $r->get('/leaders/{uuid}/projects', 'Api\LeaderController@projects');
    $r->get('/leaders/{uuid}/score', 'Api\LeaderController@score');
    $r->get('/states', 'Api\StateController@index');
    $r->get('/states/{code}', 'Api\StateController@show');
    $r->get('/states/{code}/map-data', 'Api\StateController@mapData');
    $r->get('/promises', 'Api\PromiseController@index');
    $r->get('/promises/{uuid}', 'Api\PromiseController@show');
    $r->get('/projects', 'Api\ProjectController@index');
    $r->get('/projects/{uuid}', 'Api\ProjectController@show');
    $r->get('/corruption/alerts', 'Api\CorruptionController@alerts');
    $r->get('/fake-claims', 'Api\PromiseController@fakeClaims');
    $r->get('/search', 'Api\SearchController@search');
    $r->get('/map/heatmap', 'Api\MapController@heatmap');
    $r->get('/live-updates', 'Api\LiveController@updates');

    // Auth API
    $r->post('/auth/login', 'Api\AuthController@login');
    $r->post('/auth/register', 'Api\AuthController@register');
    $r->post('/auth/logout', 'Api\AuthController@logout', ['NetaTrack\Middleware\ApiAuthMiddleware']);

    // Protected submission API
    $r->post('/reports/submit', 'Api\ReportController@submit', ['NetaTrack\Middleware\ApiAuthMiddleware']);
    $r->get('/user/reports', 'Api\UserController@reports', ['NetaTrack\Middleware\ApiAuthMiddleware']);
    $r->get('/user/profile', 'Api\UserController@profile', ['NetaTrack\Middleware\ApiAuthMiddleware']);
});

return $router;
