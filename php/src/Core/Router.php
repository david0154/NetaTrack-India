<?php
namespace NetaTrack\Core;

class Router {
    private array $routes;

    public function __construct() {
        $this->routes = require APP_PATH . '/config/routes.php';
    }

    public function dispatch(): void {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = strtok($_SERVER['REQUEST_URI'], '?');
        $uri    = rtrim($uri, '/') ?: '/';

        foreach ($this->routes as $route => $handler) {
            [$routeMethod, $routePath] = explode(' ', $route, 2);
            $pattern = preg_replace('/\{[^}]+\}/', '([^/]+)', $routePath);
            $pattern = '#^' . $pattern . '$#';

            if ($method === $routeMethod && preg_match($pattern, $uri, $matches)) {
                array_shift($matches);
                [$controllerName, $action] = $handler;

                $class = str_contains($controllerName, '\\')
                    ? 'NetaTrack\\Controllers\\' . $controllerName
                    : 'NetaTrack\\Controllers\\' . $controllerName;

                if (!class_exists($class)) {
                    $this->abort(404, "Controller $class not found");
                    return;
                }

                $controller = new $class();
                if (!method_exists($controller, $action)) {
                    $this->abort(404, "Action $action not found");
                    return;
                }

                call_user_func_array([$controller, $action], $matches);
                return;
            }
        }

        $this->abort(404);
    }

    private function abort(int $code, string $message = ''): void {
        http_response_code($code);
        if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
            header('Content-Type: application/json');
            echo json_encode(['error' => $message ?: 'Not Found', 'code' => $code]);
        } else {
            $viewFile = APP_PATH . "/views/errors/$code.php";
            if (file_exists($viewFile)) require $viewFile;
            else echo "<h1>$code Error</h1><p>$message</p>";
        }
    }
}
