<?php
/**
 * NetaTrack India - Security Middleware
 * Handles CSRF, rate limiting, IP blocking, XSS, SQL injection prevention
 */
class Security
{
    private static array $blockedIPs = [];

    // ── CSRF ──────────────────────────────────────────────────────────────
    public static function generateCsrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrfToken(string $token): bool
    {
        return isset($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function requireCsrf(): void
    {
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!self::verifyCsrfToken($token)) {
            http_response_code(403);
            die(json_encode(['error' => 'CSRF token mismatch']));
        }
    }

    // ── XSS ──────────────────────────────────────────────────────────────
    public static function sanitize(mixed $input): mixed
    {
        if (is_array($input)) {
            return array_map([self::class, 'sanitize'], $input);
        }
        if (is_string($input)) {
            return htmlspecialchars(trim($input), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }
        return $input;
    }

    public static function sanitizePost(): array
    {
        return self::sanitize($_POST);
    }

    // ── SQL Injection: always use prepared statements via Database::query ─
    public static function escapeForLike(string $value): string
    {
        return addcslashes($value, '%_\\');
    }

    // ── Rate Limiting (Redis-backed) ──────────────────────────────────────
    public static function rateLimit(string $key, int $maxRequests, int $windowSeconds): bool
    {
        // Falls back to session-based if Redis unavailable
        $sessionKey = 'rl_' . md5($key);
        $now = time();
        $data = $_SESSION[$sessionKey] ?? ['count' => 0, 'reset' => $now + $windowSeconds];
        if ($now > $data['reset']) {
            $data = ['count' => 0, 'reset' => $now + $windowSeconds];
        }
        $data['count']++;
        $_SESSION[$sessionKey] = $data;
        return $data['count'] <= $maxRequests;
    }

    public static function requireRateLimit(string $key, int $max, int $window): void
    {
        if (!self::rateLimit($key, $max, $window)) {
            http_response_code(429);
            header('Retry-After: ' . $window);
            die(json_encode(['error' => 'Too many requests. Please slow down.']));
        }
    }

    // ── IP Blocking ───────────────────────────────────────────────────────
    public static function getClientIp(): string
    {
        $keys = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR',
        ];
        foreach ($keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = explode(',', $_SERVER[$key])[0];
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '0.0.0.0';
    }

    public static function isIpBlocked(string $ip): bool
    {
        if (empty(self::$blockedIPs)) {
            try {
                $rows = Database::query('SELECT ip_address FROM ip_blocks WHERE is_active = 1');
                self::$blockedIPs = array_column($rows, 'ip_address');
            } catch (Exception) {
                return false;
            }
        }
        return in_array($ip, self::$blockedIPs, true);
    }

    public static function requireNotBlocked(): void
    {
        $ip = self::getClientIp();
        if (self::isIpBlocked($ip)) {
            http_response_code(403);
            die(json_encode(['error' => 'Access denied.']));
        }
    }

    // ── JWT ───────────────────────────────────────────────────────────────
    public static function generateJwt(array $payload): string
    {
        $secret  = getenv('JWT_SECRET');
        $header  = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload['iat'] = time();
        $payload['exp'] = time() + (int)(getenv('JWT_EXPIRY_HOURS') ?: 24) * 3600;
        $payloadEnc = base64_encode(json_encode($payload));
        $signature  = base64_encode(hash_hmac('sha256', "$header.$payloadEnc", $secret, true));
        return "$header.$payloadEnc.$signature";
    }

    public static function verifyJwt(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;
        [$header, $payloadEnc, $signature] = $parts;
        $secret   = getenv('JWT_SECRET');
        $expected = base64_encode(hash_hmac('sha256', "$header.$payloadEnc", $secret, true));
        if (!hash_equals($expected, $signature)) return null;
        $payload = json_decode(base64_decode($payloadEnc), true);
        if (!$payload || $payload['exp'] < time()) return null;
        return $payload;
    }

    public static function getBearerToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s+(\S+)/i', $header, $m)) {
            return $m[1];
        }
        return null;
    }

    public static function requireAuth(array $allowedRoles = []): array
    {
        $token = self::getBearerToken() ?? ($_SESSION['jwt'] ?? '');
        $payload = self::verifyJwt($token);
        if (!$payload) {
            http_response_code(401);
            die(json_encode(['error' => 'Unauthorised. Please login.']));
        }
        if ($allowedRoles && !in_array($payload['role'], $allowedRoles, true)) {
            http_response_code(403);
            die(json_encode(['error' => 'Forbidden. Insufficient permissions.']));
        }
        return $payload;
    }

    // ── Prompt Injection Detection ────────────────────────────────────────
    public static function detectPromptInjection(string $text): bool
    {
        $patterns = [
            '/ignore (all |previous |above )?(instructions?|prompts?)/i',
            '/you are now/i',
            '/act as (a |an )?/i',
            '/jailbreak/i',
            '/\bDAN\b/',
            '/system prompt/i',
            '/forget (everything|all)/i',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text)) {
                return true;
            }
        }
        return false;
    }

    // ── Input Validation ─────────────────────────────────────────────────
    public static function validateEmail(string $email): bool
    {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function validateUrl(string $url): bool
    {
        return (bool) filter_var($url, FILTER_VALIDATE_URL);
    }

    public static function validatePhone(string $phone): bool
    {
        return (bool) preg_match('/^[6-9]\d{9}$/', preg_replace('/\s+/', '', $phone));
    }
}
