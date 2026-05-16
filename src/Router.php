<?php
/**
 * NetaTrack India - Simple PHP Router
 */
class Router
{
    private array $routes = [];
    private ?string $prefix = '';

    public function get(string $path, callable $handler): self
    {
        $this->routes[] = ['GET', $this->prefix . $path, $handler];
        return $this;
    }

    public function post(string $path, callable $handler): self
    {
        $this->routes[] = ['POST', $this->prefix . $path, $handler];
        return $this;
    }

    public function put(string $path, callable $handler): self
    {
        $this->routes[] = ['PUT', $this->prefix . $path, $handler];
        return $this;
    }

    public function delete(string $path, callable $handler): self
    {
        $this->routes[] = ['DELETE', $this->prefix . $path, $handler];
        return $this;
    }

    public function group(string $prefix, callable $callback): self
    {
        $previous = $this->prefix;
        $this->prefix = $previous . $prefix;
        $callback($this);
        $this->prefix = $previous;
        return $this;
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri    = rtrim($uri, '/') ?: '/';

        foreach ($this->routes as [$routeMethod, $routePath, $handler]) {
            $pattern = '#^' . preg_replace('/\{([a-z_]+)\}/', '(?P<$1>[^/]+)', $routePath) . '$#';
            if ($routeMethod === $method && preg_match($pattern, $uri, $matches)) {
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                header('Content-Type: application/json; charset=utf-8');
                $result = call_user_func($handler, $params);
                if (is_array($result) || is_object($result)) {
                    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }
                return;
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'Route not found', 'path' => $uri]);
    }
}
