<?php
namespace NetaTrack\Middleware;

use NetaTrack\Core\Auth;
use NetaTrack\Core\Request;
use NetaTrack\Core\Response;

class CsrfMiddleware
{
    private array $except = ['/api/', '/webhook/'];

    public function handle($request, callable $next): mixed
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        // Skip for GET, HEAD, OPTIONS
        if (in_array($method, ['GET', 'HEAD', 'OPTIONS'])) {
            return $next($request);
        }

        // Skip for API routes
        foreach ($this->except as $except) {
            if (str_starts_with($uri, $except)) {
                return $next($request);
            }
        }

        $token = $_POST['_csrf'] ?? Request::getInstance()->header('X-CSRF-Token') ?? '';

        if (!Auth::getInstance()->verifyCsrf($token)) {
            if (Request::getInstance()->isAjax()) {
                Response::error('CSRF token mismatch', 419);
            } else {
                Response::abort(419, 'CSRF token mismatch. Please refresh the page.');
            }
            return false;
        }

        return $next($request);
    }
}
