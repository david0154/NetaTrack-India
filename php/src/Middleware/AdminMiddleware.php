<?php
namespace NetaTrack\Middleware;

use NetaTrack\Core\Auth;
use NetaTrack\Core\Response;

class AdminMiddleware
{
    public function handle($request, callable $next): mixed
    {
        $auth = Auth::getInstance();
        if (!$auth->check()) {
            Response::redirect('/admin/login');
            return false;
        }
        if (!$auth->isAdmin()) {
            Response::abort(403, 'Access denied. Admin privileges required.');
            return false;
        }
        return $next($request);
    }
}
