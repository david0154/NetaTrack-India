<?php
namespace NetaTrack\Core;

use NetaTrack\Models\User;

class Auth
{
    private static ?array $userCache = null;

    public function check(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public function isAdmin(): bool
    {
        return ($_SESSION['user_role'] ?? '') === 'admin';
    }

    public function isModerator(): bool
    {
        return in_array($_SESSION['user_role'] ?? '', ['admin', 'moderator']);
    }

    public function user(): ?array
    {
        if (!$this->check()) return null;
        if (self::$userCache !== null) return self::$userCache;
        $model = new User();
        self::$userCache = $model->find((int)$_SESSION['user_id']);
        return self::$userCache;
    }

    public function id(): ?int
    {
        return $this->check() ? (int)$_SESSION['user_id'] : null;
    }

    public function role(): string
    {
        return $_SESSION['user_role'] ?? 'guest';
    }

    public function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        self::$userCache = $user;
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        self::$userCache = null;
    }
}
