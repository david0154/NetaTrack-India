<?php
namespace NetaTrack\Middleware;

use NetaTrack\Core\Auth;
use NetaTrack\Core\Response;

/**
 * NetaTrack India - Auth Middleware (requires login)
 */
class AuthMiddleware
{
    public function handle(): void
    {
        if (!Auth::getInstance()->check()) {
            if (str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/')) {
                Response::error('Unauthenticated', 401);
            }
            flash('error', 'Please log in to continue.');
            Response::redirect(url('auth/login'));
        }
    }
}
