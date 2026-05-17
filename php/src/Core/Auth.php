<?php
namespace NetaTrack\Core;

/**
 * NetaTrack India - Auth Manager
 */
class Auth
{
    private static ?Auth $instance = null;
    private ?array $user = null;

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) self::$instance = new self();
        return self::$instance;
    }

    public function init(): void
    {
        $cfg = require ROOT_PATH . '/config/auth.php';
        if (session_status() === PHP_SESSION_NONE) {
            session_name($cfg['session_name']);
            session_set_cookie_params([
                'lifetime' => $cfg['session_lifetime'],
                'path'     => '/',
                'secure'   => !APP_DEBUG,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }

        // Restore user from session
        if (!empty($_SESSION['user_id'])) {
            $db = Database::getInstance();
            $this->user = $db->fetch(
                'SELECT * FROM users WHERE id=? AND status=? LIMIT 1',
                [$_SESSION['user_id'], 'active']
            );
        }
    }

    public function attempt(string $email, string $password): bool
    {
        $db   = Database::getInstance();
        $user = $db->fetch('SELECT * FROM users WHERE email=? AND status=? LIMIT 1', [$email, 'active']);
        if (!$user || !password_verify($password, $user['password'])) return false;

        $this->login($user);
        return true;
    }

    public function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $this->user = $user;

        // Update last login
        Database::getInstance()->query(
            'UPDATE users SET last_login_at=? WHERE id=?',
            [date('Y-m-d H:i:s'), $user['id']]
        );
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        $this->user = null;
    }

    public function check(): bool   { return $this->user !== null; }
    public function user(): ?array  { return $this->user; }
    public function id(): ?int      { return $this->user ? (int)$this->user['id'] : null; }
    public function role(): ?string { return $this->user['role'] ?? null; }

    public function isAdmin(): bool
    {
        return in_array($this->role(), ['super_admin','admin','moderator','editor']);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role() === 'super_admin';
    }

    public function hasRole(string $role): bool
    {
        $cfg    = require ROOT_PATH . '/config/auth.php';
        $roles  = $cfg['roles'];
        $myPerm = $roles[$this->role()] ?? 0;
        $reqPerm= $roles[$role] ?? 999;
        return $myPerm >= $reqPerm;
    }
}
