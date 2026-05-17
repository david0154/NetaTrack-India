<?php
namespace NetaTrack\Middleware;

use NetaTrack\Core\Request;
use NetaTrack\Core\Response;

/**
 * NetaTrack India - Rate Limit Middleware
 */
class RateLimitMiddleware
{
    private int $maxRequests = 60;
    private int $window      = 60; // seconds

    public function handle(): void
    {
        $ip  = Request::ip();
        $key = 'rl_' . md5($ip);
        $now = time();

        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'start' => $now];
        }

        if ($now - $_SESSION[$key]['start'] > $this->window) {
            $_SESSION[$key] = ['count' => 0, 'start' => $now];
        }

        $_SESSION[$key]['count']++;

        if ($_SESSION[$key]['count'] > $this->maxRequests) {
            Response::error('Too many requests. Please slow down.', 429);
        }
    }
}
