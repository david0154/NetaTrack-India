<?php
namespace NetaTrack\Core;

class Router
{
    private static ?self $instance = null;
    private array $routes = [];
    private string $basePath = '';

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) self::$instance = new self();
        return self::$instance;
    }

    public function setBasePath(string $path): void { $this->basePath = rtrim($path, '/'); }

    public function get(string $path, string|callable $handler): void
    {
        $this->addRoute('GET', $path, $handler);
    }

    public function post(string $path, string|callable $handler): void
    {
        $this->addRoute('POST', $path, $handler);
    }

    public function group(string $prefix, array $options, callable $callback): void
    {
        $sub = new self();
        $callback($sub);
        foreach ($sub->routes as $route) {
            $route['path']       = $prefix . ($route['path'] ? '/' . $route['path'] : '');
            $route['middleware'] = array_merge($options['middleware'] ?? [], $route['middleware'] ?? []);
            $this->routes[] = $route;
        }
    }

    public function fallback(callable $handler): void
    {
        $this->routes[] = ['method'=>'FALLBACK','path'=>'*','handler'=>$handler,'middleware'=>[]];
    }

    private function addRoute(string $method, string $path, string|callable $handler): void
    {
        $this->routes[] = ['method'=>$method, 'path'=>$path, 'handler'=>$handler, 'middleware'=>[]];
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $uri    = trim($uri, '/');

        // Strip base path
        if ($this->basePath) {
            $base = ltrim($this->basePath, '/');
            if (str_starts_with($uri, $base)) {
                $uri = ltrim(substr($uri, strlen($base)), '/');
            }
        }

        $fallback = null;

        foreach ($this->routes as $route) {
            if ($route['method'] === 'FALLBACK') { $fallback = $route; continue; }
            if ($route['method'] !== $method)    continue;

            [$matched, $params] = $this->matchPath($route['path'], $uri);
            if (!$matched) continue;

            // Middleware
            foreach ($route['middleware'] as $mw) {
                $this->runMiddleware($mw);
            }

            $req = new Request();
            $res = new Response();
            $this->callHandler($route['handler'], $req, $res, $params);
            return;
        }

        if ($fallback) {
            ($fallback['handler'])();
        } else {
            http_response_code(404);
            echo '404 Not Found';
        }
    }

    private function matchPath(string $pattern, string $uri): array
    {
        if ($pattern === $uri) return [true, []];

        $patternParts = explode('/', $pattern);
        $uriParts     = explode('/', $uri);

        if (count($patternParts) !== count($uriParts)) return [false, []];

        $params = [];
        foreach ($patternParts as $i => $part) {
            if (str_starts_with($part, '{') && str_ends_with($part, '}')) {
                $params[trim($part, '{}')] = $uriParts[$i];
            } elseif ($part !== $uriParts[$i]) {
                return [false, []];
            }
        }
        return [true, $params];
    }

    private function runMiddleware(string $name): void
    {
        switch ($name) {
            case 'auth':
                if (!auth()->check()) {
                    (new Response())->redirect('auth/login');
                    exit;
                }
                break;
            case 'admin':
                if (!auth()->isAdmin()) {
                    (new Response())->redirect('admin/auth/login');
                    exit;
                }
                break;
        }
    }

    private function callHandler(string|callable $handler, Request $req, Response $res, array $params): void
    {
        if (is_callable($handler)) {
            $handler($req, $res, ...$params);
            return;
        }
        [$class, $method] = explode('@', $handler);
        $namespace = 'NetaTrack\\Controllers\\';
        $fqcn      = $namespace.$class;
        $ctrl      = new $fqcn();
        $ctrl->$method($req, $res, ...$params);
    }
}
