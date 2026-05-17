<?php
/**
 * NetaTrack India - API Routes
 */

use NetaTrack\Core\Router;

Router::group(['prefix' => 'api/v1', 'name' => 'api.'], function () {
    // Stats
    Router::get('/stats',            [\NetaTrack\Controllers\Api\StatsController::class, 'index'], 'stats');
    // Leaders
    Router::get('/leaders',          [\NetaTrack\Controllers\Api\LeaderApiController::class, 'index'],  'leaders');
    Router::get('/leaders/{slug}',   [\NetaTrack\Controllers\Api\LeaderApiController::class, 'show'],   'leaders.show');
    // Search
    Router::get('/search',           [\NetaTrack\Controllers\Api\SearchApiController::class, 'index'],  'search');
    // Map data
    Router::get('/map/states',       [\NetaTrack\Controllers\Api\MapController::class, 'states'],       'map.states');
    // Submit report (public)
    Router::post('/reports',         [\NetaTrack\Controllers\Api\ReportApiController::class, 'store'],  'reports.store');
});
