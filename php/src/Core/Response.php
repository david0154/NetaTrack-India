<?php
namespace NetaTrack\Core;

class Response
{
    public static function json(mixed $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function success(mixed $data = null, string $message = 'Success', int $code = 200): void
    {
        static::json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    public static function error(string $message, int $code = 400, array $errors = []): void
    {
        static::json([
            'success' => false,
            'message' => $message,
            'errors'  => $errors,
        ], $code);
    }

    public static function redirect(string $url, int $code = 302): void
    {
        http_response_code($code);
        header('Location: ' . $url);
        exit;
    }

    public static function view(string $view, array $data = [], int $code = 200): void
    {
        http_response_code($code);
        extract($data);
        $viewPath = ROOT_PATH . '/views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewPath)) {
            static::abort(404, "View not found: $view");
        }
        require $viewPath;
        exit;
    }

    public static function abort(int $code, string $message = ''): void
    {
        http_response_code($code);
        $isApi = str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/api/');
        if ($isApi) {
            static::json(['success' => false, 'message' => $message ?: "HTTP $code"], $code);
        } else {
            $viewPath = ROOT_PATH . '/views/errors/' . $code . '.php';
            if (file_exists($viewPath)) {
                require $viewPath;
            } else {
                echo "<h1>Error $code</h1><p>$message</p>";
            }
        }
        exit;
    }

    public static function withFlash(string $url, string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
        static::redirect($url);
    }
}
