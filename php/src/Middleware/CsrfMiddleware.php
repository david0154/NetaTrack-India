<?php
namespace NetaTrack\Middleware;

use NetaTrack\Core\Response;

/**
 * NetaTrack India - CSRF Middleware
 */
class CsrfMiddleware
{
    private array $except = [
        '/api/webhook',
        '/api/scraper/callback',
    ];

    public function handle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        foreach ($this->except as $ex) {
            if (str_starts_with($uri, $ex)) return;
        }

        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(419);
            die('CSRF token mismatch.');
        }
    }
}
