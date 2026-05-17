<?php
namespace NetaTrack\Helpers;

use NetaTrack\Models\User;

class Auth {
    public static function login(array $user): void {
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_email']= $user['email'];
        session_regenerate_id(true);
    }

    public static function logout(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function check(): bool { return !empty($_SESSION['user_id']); }
    public static function isAdmin(): bool { return ($_SESSION['user_role'] ?? '') === 'admin'; }
    public static function id(): ?int { return $_SESSION['user_id'] ?? null; }
    public static function user(): ?array {
        if (!static::check()) return null;
        return User::find($_SESSION['user_id']);
    }
    public static function name(): string { return $_SESSION['user_name'] ?? 'Guest'; }
    public static function role(): string { return $_SESSION['user_role'] ?? 'guest'; }
}
