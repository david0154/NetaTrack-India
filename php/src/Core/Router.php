<?php
namespace NetaTrack\Core;

/**
 * NetaTrack India - Router
 */
class Router
{
    private static array $routes = [];
    private static array $namedRoutes = [];
    private static array $middleware = [];
    private static string $prefix = '';
    private static string $prefixName = '';

    public static function get(string $path, array|callable $handler, ?string $name = null): void
    {
        self::add('GET', $path, $handler, $name);
    }

    public static function post(string $path, array|callable $handler, ?string $name = null): void
    {
        self::add('POST', $path, $handler, $name);
    }

    public static function group(array $attrs, callable $cb): void
    {
        $prevPrefix     = self::$prefix;
        $prevPrefixName = self::$prefixName;
        $prevMiddleware = self::$middleware;

        if (isset($attrs['prefix'])) self::$prefix .= '/' . trim($attrs['prefix'], '/');
        if (isset($attrs['name']))   self::$prefixName .= $attrs['name'];
        if (isset($attrs['middleware'])) {
            self::$middleware = array_merge(self::$middleware, (array)$attrs['middleware']);
        }

        $cb();

        self::$prefix     = $prevPrefix;
        self::$prefixName = $prevPrefixName;
        self::$middleware = $prevMiddleware;
    }

    private static function add(string $method, string $path, array|callable $handler, ?string $name): void
    {
        $path = self::$prefix . '/' . trim($path, '/');
        $path = rtrim($path, '/')  ?: '/';
        $route = [
            'method'     => $method,
            'path'       => $path,
            'handler'    => $handler,
            'middleware' => self::$middleware,
            'pattern'    => self::toRegex($path),
        ];
        self::$routes[] = $route;
        if ($name) {
            self::$namedRoutes[self::$prefixName . $name] = $path;
        }
    }

    private static function toRegex(string $path): string
    {
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    public static function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $uri    = '/' . trim($uri, '/');
        if ($uri !== '/') $uri = rtrim($uri, '/');

        foreach (self::$routes as $route) {
            if ($route['method'] !== $method) continue;
            if (!preg_match($route['pattern'], $uri, $matches)) continue;

            // Run middleware
            foreach ($route['middleware'] as $mw) {
                $mwClass = 'NetaTrack\\Middleware\\' . $mw;
                if (class_exists($mwClass)) {
                    (new $mwClass())->handle();
                }
            }

            // Extract named params
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

            // Dispatch handler
            if (is_callable($route['handler'])) {
                call_user_func_array($route['handler'], $params);
            } elseif (is_array($route['handler'])) {
                [$class, $method] = $route['handler'];
                $controller = new $class();
                call_user_func_array([$controller, $method], $params);
            }
            return;
        }

        // 404
        http_response_code(404);
        $view = VIEWS_PATH . '/errors/404.php';
        if (file_exists($view)) require $view;
        else echo '<h1>404 - Page Not Found</h1>';
    }

    public static function route(string $name, array $params = []): string
    {
        $path = self::$namedRoutes[$name] ?? '/';
        foreach ($params as $k => $v) {
            $path = str_replace("{{$k}}", $v, $path);
        }
        return $path;
    }
}
