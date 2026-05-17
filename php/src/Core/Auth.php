<?php
namespace NetaTrack\Core;

use NetaTrack\Models\User;

class Auth
{
    private static ?Auth $instance = null;
    private ?array $user = null;

    public static function getInstance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name('netatrack_session');
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'secure'   => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
            session_start();
        }

        if (!empty($_SESSION['user_id'])) {
            $this->user = (new User())->find($_SESSION['user_id']);
        }
    }

    public function attempt(string $email, string $password, bool $remember = false): bool
    {
        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        if ($user['is_banned']) {
            throw new \RuntimeException('Your account has been banned: ' . ($user['ban_reason'] ?? 'No reason given'));
        }

        $this->loginUser($user);

        if ($remember) {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_token', $token, time() + 30 * 86400, '/', '', isset($_SERVER['HTTPS']), true);
            $userModel->update($user['id'], ['remember_token' => hash('sha256', $token)]);
        }

        return true;
    }

    private function loginUser(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['_token'] = bin2hex(random_bytes(32));
        $this->user = $user;

        (new User())->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);
    }

    public function logout(): void
    {
        $this->user = null;
        $_SESSION = [];
        session_destroy();
        setcookie('remember_token', '', time() - 3600, '/');
    }

    public function check(): bool
    {
        return $this->user !== null;
    }

    public function user(): ?array
    {
        return $this->user;
    }

    public function id(): ?int
    {
        return $this->user ? (int)$this->user['id'] : null;
    }

    public function isAdmin(): bool
    {
        return $this->user && in_array($this->user['role_id'], [1, 2]);
    }

    public function isSuperAdmin(): bool
    {
        return $this->user && (int)$this->user['role_id'] === 1;
    }

    public function isModerator(): bool
    {
        return $this->user && in_array($this->user['role_id'], [1, 2, 3]);
    }

    public function csrfToken(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public function verifyCsrf(string $token): bool
    {
        return hash_equals($_SESSION['_csrf'] ?? '', $token);
    }

    public function register(array $data): int
    {
        $db = Database::getInstance();

        $exists = $db->selectOne('SELECT id FROM users WHERE email = ?', [$data['email']]);
        if ($exists) {
            throw new \RuntimeException('Email already registered');
        }

        return $db->insert('users', [
            'uuid'       => $this->generateUuid(),
            'name'       => $data['name'],
            'email'      => $data['email'],
            'password'   => password_hash($data['password'], PASSWORD_ARGON2ID),
            'role_id'    => 4,
            'state'      => $data['state'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function generateUuid(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
