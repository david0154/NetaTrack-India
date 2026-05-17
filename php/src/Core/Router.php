<?php
namespace NetaTrack\Core;

class Router {
    private array $routes;

    public function __construct() {
        $this->routes = require APP_PATH . '/config/routes.php';
    }

    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $this->normalizeUri($_SERVER['REQUEST_URI'] ?? '/');

        foreach ($this->routes as $route => $handler) {
            [$routeMethod, $routePath] = explode(' ', $route, 2);
            $normalizedRoutePath = $this->normalizePath($routePath);
            $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $normalizedRoutePath);
            $pattern = '#^' . $pattern . '$#';

            if ($method === $routeMethod && preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                [$controllerName, $action] = $handler;
                $class = 'NetaTrack\\Controllers\\' . ltrim($controllerName, '\\');

                if (!class_exists($class)) {
                    $this->abort(404, "Controller {$class} not found");
                    return;
                }

                $controller = new $class();
                if (!method_exists($controller, $action)) {
                    $this->abort(404, "Action {$action} not found in {$class}");
                    return;
                }

                call_user_func_array([$controller, $action], $matches);
                return;
            }
        }

        $this->abort(404, 'Route not found');
    }

    private function normalizeUri(string $uri): string {
        $path = strtok($uri, '?') ?: '/';
        $path = preg_replace('#/+#', '/', $path);

        if (str_starts_with($path, '/php/index.php')) {
            $path = substr($path, strlen('/php/index.php')) ?: '/';
        }
        if (str_starts_with($path, '/index.php')) {
            $path = substr($path, strlen('/index.php')) ?: '/';
        }

        return $this->normalizePath($path);
    }

    private function normalizePath(string $path): string {
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }

    private function abort(int $code, string $message = ''): void {
        http_response_code($code);

        if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['error' => $message ?: 'Not Found', 'code' => $code], JSON_UNESCAPED_UNICODE);
            return;
        }

        $title = $code . ' Error';
        $viewFile = APP_PATH . "/views/errors/{$code}.php";
        if (file_exists($viewFile)) {
            require $viewFile;
            return;
        }

        echo "<h1>{$code} Error</h1><p>" . htmlspecialchars($message ?: 'An error occurred', ENT_QUOTES, 'UTF-8') . "</p>";
    }
}
