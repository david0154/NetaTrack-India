<?php
require_once __DIR__ . '/Database.php';

class Auth
{
    private const SESSION_KEY = 'nt_user';
    private const JWT_SECRET_KEY = 'jwt_secret';

    public static function attempt(string $email, string $password): bool
    {
        $user = Database::queryOne('SELECT * FROM users WHERE email = ? AND is_banned = 0', [$email]);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }
        Database::execute('UPDATE users SET last_login = NOW() WHERE id = ?', [$user['id']]);
        unset($user['password_hash']);
        $_SESSION[self::SESSION_KEY] = $user;
        return true;
    }

    public static function register(string $name, string $email, string $password, string $role = 'user'): int
    {
        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $token = bin2hex(random_bytes(32));
        return Database::execute(
            'INSERT INTO users (name, email, password_hash, role, email_verify_token) VALUES (?, ?, ?, ?, ?)',
            [$name, $email, $hash, $role, $token]
        );
    }

    public static function user(): ?array
    {
        return $_SESSION[self::SESSION_KEY] ?? null;
    }

    public static function check(): bool
    {
        return isset($_SESSION[self::SESSION_KEY]);
    }

    public static function is(string $role): bool
    {
        $user = self::user();
        if (!$user) return false;
        if ($user['role'] === 'superadmin') return true;
        return $user['role'] === $role;
    }

    public static function isAdmin(): bool
    {
        return self::is('admin') || self::is('superadmin');
    }

    public static function requireAdmin(): void
    {
        if (!self::isAdmin()) {
            http_response_code(403);
            die(json_encode(['error' => 'Unauthorized']));
        }
    }

    public static function generateJwt(array $payload): string
    {
        $secret = getenv('JWT_SECRET') ?: 'netatrack-secret-change-in-production';
        $header  = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $body    = base64_encode(json_encode(array_merge($payload, ['iat' => time(), 'exp' => time() + 86400])));
        $sig     = base64_encode(hash_hmac('sha256', "$header.$body", $secret, true));
        return "$header.$body.$sig";
    }

    public static function verifyJwt(string $token): ?array
    {
        $secret = getenv('JWT_SECRET') ?: 'netatrack-secret-change-in-production';
        $parts  = explode('.', $token);
        if (count($parts) !== 3) return null;
        [$header, $body, $sig] = $parts;
        $expected = base64_encode(hash_hmac('sha256', "$header.$body", $secret, true));
        if (!hash_equals($expected, $sig)) return null;
        $payload = json_decode(base64_decode($body), true);
        if (!$payload || $payload['exp'] < time()) return null;
        return $payload;
    }

    public static function logout(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
        session_destroy();
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
}
