<?php
namespace NetaTrack\Core;

/**
 * NetaTrack India - HTTP Request Helper
 */
class Request
{
    public static function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public static function uri(): string
    {
        return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return htmlspecialchars(strip_tags($_GET[$key] ?? $default ?? ''));
    }

    public static function post(string $key, mixed $default = null): mixed
    {
        $val = $_POST[$key] ?? $default;
        if (is_string($val)) return htmlspecialchars(strip_tags($val));
        return $val;
    }

    public static function input(string $key, mixed $default = null): mixed
    {
        return self::post($key) ?? self::get($key, $default);
    }

    public static function all(): array
    {
        return array_merge($_GET ?? [], $_POST ?? []);
    }

    public static function isPost(): bool { return self::method() === 'POST'; }
    public static function isGet():  bool { return self::method() === 'GET'; }
    public static function isAjax(): bool
    {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    public static function ip(): string
    {
        return $_SERVER['HTTP_CF_CONNECTING_IP']
            ?? $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['REMOTE_ADDR']
            ?? '0.0.0.0';
    }

    public static function bearerToken(): ?string
    {
        $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (str_starts_with($auth, 'Bearer ')) return substr($auth, 7);
        return null;
    }

    public static function json(): ?array
    {
        $body = file_get_contents('php://input');
        return json_decode($body, true);
    }
}
