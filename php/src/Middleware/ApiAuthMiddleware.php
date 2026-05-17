<?php
namespace NetaTrack\Middleware;

use NetaTrack\Core\Auth;
use NetaTrack\Core\Request;
use NetaTrack\Core\Response;
use NetaTrack\Core\Database;

class ApiAuthMiddleware
{
    public function handle($request, callable $next): mixed
    {
        $token = Request::getInstance()->bearerToken();

        if (!$token) {
            Response::error('Unauthorized - Bearer token required', 401);
            return false;
        }

        // Check API key
        $db = Database::getInstance();
        $keyHash = hash('sha256', $token);
        $apiKey = $db->selectOne('SELECT * FROM api_keys WHERE key_hash = ? AND is_active = 1', [$keyHash]);

        if ($apiKey) {
            if ($apiKey['expires_at'] && strtotime($apiKey['expires_at']) < time()) {
                Response::error('API key expired', 401);
                return false;
            }
            $db->query('UPDATE api_keys SET calls_count = calls_count + 1, last_used_at = NOW() WHERE id = ?', [$apiKey['id']]);
            return $next($request);
        }

        // Check JWT session token
        $appKey = getenv('APP_KEY') ?: 'netatrack_secret';
        $payload = \NetaTrack\Helpers\Security::validateJwt($token, $appKey);
        if ($payload && isset($payload['user_id'])) {
            $_SESSION['user_id'] = $payload['user_id'];
            Auth::getInstance()->init();
            if (Auth::getInstance()->check()) {
                return $next($request);
            }
        }

        Response::error('Invalid or expired token', 401);
        return false;
    }
}
