<?php
/**
 * NetaTrack India — Global Helper Functions
 */

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        static $configs = [];
        $parts = explode('.', $key);
        $file  = $parts[0];
        if (!isset($configs[$file])) {
            $path = BASE_PATH . '/config/' . $file . '.php';
            $configs[$file] = file_exists($path) ? require $path : [];
        }
        $val = $configs[$file];
        foreach (array_slice($parts, 1) as $part) {
            if (!is_array($val) || !array_key_exists($part, $val)) return $default;
            $val = $val[$part];
        }
        return $val ?? $default;
    }
}

if (!function_exists('auth')) {
    function auth(): \NetaTrack\Core\Auth
    {
        static $instance = null;
        if ($instance === null) $instance = new \NetaTrack\Core\Auth();
        return $instance;
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        $base = rtrim(config('app.url', ''), '/');
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string
    {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('e')) {
    function e(mixed $value): string
    {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('flash')) {
    function flash(string $key, ?string $message = null): ?string
    {
        if ($message !== null) {
            $_SESSION['_flash'][$key] = $message;
            return null;
        }
        $val = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $val;
    }
}

if (!function_exists('old')) {
    function old(string $key, string $default = ''): string
    {
        return e($_SESSION['_old_input'][$key] ?? $default);
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        $token = $_SESSION['_csrf_token'];
        return '<input type="hidden" name="_csrf_token" value="' . $token . '">';
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }
}

if (!function_exists('truncate')) {
    function truncate(string $text, int $length = 100, string $suffix = '...'): string
    {
        if (mb_strlen($text) <= $length) return $text;
        return mb_substr($text, 0, $length) . $suffix;
    }
}

if (!function_exists('timeAgo')) {
    function timeAgo(string $datetime): string
    {
        $now  = new \DateTime();
        $then = new \DateTime($datetime);
        $diff = $now->diff($then);

        if ($diff->y > 0) return $diff->y . ' year'   . ($diff->y > 1 ? 's' : '') . ' ago';
        if ($diff->m > 0) return $diff->m . ' month'  . ($diff->m > 1 ? 's' : '') . ' ago';
        if ($diff->d > 0) return $diff->d . ' day'    . ($diff->d > 1 ? 's' : '') . ' ago';
        if ($diff->h > 0) return $diff->h . ' hour'   . ($diff->h > 1 ? 's' : '') . ' ago';
        if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
        return 'just now';
    }
}

if (!function_exists('formatCrore')) {
    function formatCrore(float $amount): string
    {
        if ($amount >= 100) return '₹' . number_format($amount / 100, 2) . 'K Cr';
        return '₹' . number_format($amount, 2) . ' Cr';
    }
}

if (!function_exists('scoreColor')) {
    function scoreColor(int $score): string
    {
        return match(true) {
            $score >= 90 => '#22c55e',
            $score >= 75 => '#06b6d4',
            $score >= 50 => '#f59e0b',
            default      => '#ef4444',
        };
    }
}

if (!function_exists('dd')) {
    function dd(mixed ...$vars): void
    {
        echo '<pre style="background:#1e293b;color:#94a3b8;padding:1rem;font-size:.85rem;border-radius:8px;margin:1rem">';
        foreach ($vars as $v) { var_dump($v); echo "\n"; }
        echo '</pre>';
        exit;
    }
}
