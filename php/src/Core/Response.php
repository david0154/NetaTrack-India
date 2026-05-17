<?php
namespace NetaTrack\Core;

/**
 * NetaTrack India - HTTP Response Helper
 */
class Response
{
    public static function json(mixed $data, int $status = 200, array $headers = []): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        foreach ($headers as $k => $v) header("{$k}: {$v}");
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function success(mixed $data = null, string $message = 'Success', int $status = 200): void
    {
        self::json(['success' => true, 'message' => $message, 'data' => $data], $status);
    }

    public static function error(string $message = 'Error', int $status = 400, array $errors = []): void
    {
        self::json(['success' => false, 'message' => $message, 'errors' => $errors], $status);
    }

    public static function redirect(string $url, int $status = 302): void
    {
        http_response_code($status);
        header("Location: {$url}");
        exit;
    }

    public static function notFound(): void
    {
        http_response_code(404);
        $view = VIEWS_PATH . '/errors/404.php';
        if (file_exists($view)) require $view;
        else echo '<h1>404 Not Found</h1>';
        exit;
    }

    public static function forbidden(): void
    {
        http_response_code(403);
        $view = VIEWS_PATH . '/errors/403.php';
        if (file_exists($view)) require $view;
        else echo '<h1>403 Forbidden</h1>';
        exit;
    }
}
