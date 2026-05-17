<?php
namespace NetaTrack\Middleware;

use NetaTrack\Core\Auth;
use NetaTrack\Core\Response;

/**
 * NetaTrack India - Admin Middleware
 */
class AdminMiddleware
{
    public function handle(): void
    {
        $auth = Auth::getInstance();
        if (!$auth->check()) {
            flash('error', 'Please log in to access the admin panel.');
            Response::redirect(url('admin/login'));
        }
        if (!$auth->isAdmin()) {
            Response::forbidden();
        }
    }
}
