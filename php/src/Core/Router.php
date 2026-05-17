<?php
namespace NetaTrack\Core;

class Router
{
    private array $routes = [];
    private array $middlewares = [];
    private string $prefix = '';

    public function get(string $path, $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    public function group(string $prefix, callable $callback, array $middleware = []): void
    {
        $prevPrefix = $this->prefix;
        $this->prefix = $prevPrefix . $prefix;
        $this->middlewares = array_merge($this->middlewares, $middleware);
        $callback($this);
        $this->prefix = $prevPrefix;
    }

    private function addRoute(string $method, string $path, $handler, array $middleware): void
    {
        $fullPath = $this->prefix . $path;
        $this->routes[] = [
            'method'     => $method,
            'path'       => $fullPath,
            'pattern'    => $this->pathToPattern($fullPath),
            'handler'    => $handler,
            'middleware' => array_merge($this->middlewares, $middleware),
        ];
    }

    private function pathToPattern(string $path): string
    {
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        // Handle method override for forms
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;

            if (preg_match($route['pattern'], $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Run middleware chain
                foreach ($route['middleware'] as $mw) {
                    $middlewareInstance = new $mw();
                    $result = $middlewareInstance->handle(Request::getInstance(), function() {});
                    if ($result === false) return;
                }

                // Dispatch handler
                $this->callHandler($route['handler'], $params);
                return;
            }
        }

        // 404
        http_response_code(404);
        if ($this->isApiRequest()) {
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Route not found', 'code' => 404]);
        } else {
            require ROOT_PATH . '/views/errors/404.php';
        }
    }

    private function callHandler($handler, array $params): void
    {
        if (is_callable($handler)) {
            call_user_func_array($handler, $params);
        } elseif (is_string($handler) && str_contains($handler, '@')) {
            [$class, $method] = explode('@', $handler);
            $fullClass = 'NetaTrack\\Controllers\\' . $class;
            $controller = new $fullClass();
            call_user_func_array([$controller, $method], $params);
        } elseif (is_array($handler) && count($handler) === 2) {
            [$class, $method] = $handler;
            $controller = new $class();
            call_user_func_array([$controller, $method], $params);
        }
    }

    private function isApiRequest(): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        return str_starts_with($uri, '/api/');
    }
}
