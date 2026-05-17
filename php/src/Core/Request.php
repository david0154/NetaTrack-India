<?php
namespace NetaTrack\Core;

class Request
{
    public function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($_GET, $_POST);
    }

    public function method(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }

    public function isPost(): bool  { return $this->method() === 'POST'; }
    public function isGet():  bool  { return $this->method() === 'GET';  }
    public function isAjax(): bool  { return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'; }

    public function ip(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    public function verifyCsrf(): bool
    {
        $token = $this->post('_csrf_token') ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        return hash_equals($_SESSION['_csrf_token'] ?? '', $token);
    }

    public function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }
}
