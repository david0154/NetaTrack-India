<?php
/**
 * NetaTrack India - Global Helper Functions
 */

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $cfg = require ROOT_PATH . '/config/app.php';
        return rtrim($cfg['url'], '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('route')) {
    function route(string $name, array $params = []): string
    {
        return \NetaTrack\Core\Router::route($name, $params);
    }
}

if (!function_exists('auth')) {
    function auth(): \NetaTrack\Core\Auth
    {
        return \NetaTrack\Core\Auth::getInstance();
    }
}

if (!function_exists('db')) {
    function db(): \NetaTrack\Core\Database
    {
        return \NetaTrack\Core\Database::getInstance();
    }
}

if (!function_exists('e')) {
    function e(mixed $val): string
    {
        return htmlspecialchars((string)$val, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return '<input type="hidden" name="_token" value="' . $_SESSION['csrf_token'] . '">';
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('flash')) {
    function flash(string $key, mixed $value = null): mixed
    {
        if ($value !== null) {
            $_SESSION['flash'][$key] = $value;
            return null;
        }
        $val = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $val;
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        return $_SESSION['old'][$key] ?? $default;
    }
}

if (!function_exists('formatINR')) {
    function formatINR(float $amount): string
    {
        return '₹' . number_format($amount, 2, '.', ',');
    }
}

if (!function_exists('timeAgo')) {
    function timeAgo(string $datetime): string
    {
        $diff = time() - strtotime($datetime);
        if ($diff < 60)      return $diff . 's ago';
        if ($diff < 3600)    return round($diff/60) . 'm ago';
        if ($diff < 86400)   return round($diff/3600) . 'h ago';
        if ($diff < 2592000) return round($diff/86400) . 'd ago';
        return date('d M Y', strtotime($datetime));
    }
}

if (!function_exists('slug')) {
    function slug(string $str): string
    {
        $str = strtolower(trim($str));
        $str = preg_replace('/[^a-z0-9\-]/', '-', $str);
        return preg_replace('/-+/', '-', $str);
    }
}

if (!function_exists('truncate')) {
    function truncate(string $str, int $len = 100): string
    {
        return strlen($str) > $len ? substr($str, 0, $len) . '...' : $str;
    }
}

if (!function_exists('config')) {
    function config(string $key): mixed
    {
        [$file, $item] = explode('.', $key, 2);
        $cfg = require ROOT_PATH . '/config/' . $file . '.php';
        return $cfg[$item] ?? null;
    }
}

if (!function_exists('scoreColor')) {
    function scoreColor(int $score): string
    {
        if ($score >= 90) return 'excellent';
        if ($score >= 75) return 'good';
        if ($score >= 50) return 'average';
        return 'poor';
    }
}

if (!function_exists('corruptionLevel')) {
    function corruptionLevel(int $score): string
    {
        if ($score <= 10) return 'Very Clean';
        if ($score <= 30) return 'Minor Allegations';
        if ($score <= 60) return 'Moderate';
        return 'High Corruption Risk';
    }
}
