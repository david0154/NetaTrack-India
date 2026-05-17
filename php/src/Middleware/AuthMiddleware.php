<?php
namespace NetaTrack\Middleware;

use NetaTrack\Core\Auth;
use NetaTrack\Core\Response;

class AuthMiddleware
{
    public function handle($request, callable $next): mixed
    {
        if (!Auth::getInstance()->check()) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
            Response::redirect('/login');
            return false;
        }
        return $next($request);
    }
}
