<?php
// NetaTrack India - Global Helper Functions

if (!function_exists('setting')) {
    function setting(string $key, mixed $default = null): mixed {
        static $model = null;
        if (!$model) $model = new \NetaTrack\Models\Setting();
        return $model->get($key, $default);
    }
}

if (!function_exists('auth')) {
    function auth(): \NetaTrack\Core\Auth {
        return \NetaTrack\Core\Auth::getInstance();
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): void {
        \NetaTrack\Core\Response::redirect($url);
    }
}

if (!function_exists('view')) {
    function view(string $view, array $data = []): void {
        \NetaTrack\Core\Response::view($view, $data);
    }
}

if (!function_exists('flash')) {
    function flash(): ?array {
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string {
        $token = auth()->csrfToken();
        return "<input type='hidden' name='_csrf' value='$token'>";
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        return auth()->csrfToken();
    }
}

if (!function_exists('e')) {
    function e(mixed $val): string {
        return htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string {
        $base = rtrim(getenv('APP_URL') ?: '', '/');
        return $base . '/assets/' . ltrim($path, '/');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string {
        $base = rtrim(getenv('APP_URL') ?: '', '/');
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('formatScore')) {
    function formatScore(float $score): string {
        if ($score >= 90) return 'Excellent';
        if ($score >= 75) return 'Good';
        if ($score >= 50) return 'Average';
        return 'Poor';
    }
}

if (!function_exists('scoreColor')) {
    function scoreColor(float $score): string {
        if ($score >= 75) return '#22c55e';
        if ($score >= 50) return '#eab308';
        if ($score >= 25) return '#f97316';
        return '#ef4444';
    }
}

if (!function_exists('timeAgo')) {
    function timeAgo(string $datetime): string {
        $time = time() - strtotime($datetime);
        if ($time < 60) return 'Just now';
        if ($time < 3600) return floor($time/60) . ' minutes ago';
        if ($time < 86400) return floor($time/3600) . ' hours ago';
        if ($time < 604800) return floor($time/86400) . ' days ago';
        return date('d M Y', strtotime($datetime));
    }
}

if (!function_exists('formatIndianNumber')) {
    function formatIndianNumber(int|float $num): string {
        if ($num >= 10000000) return round($num/10000000, 1) . ' Cr';
        if ($num >= 100000) return round($num/100000, 1) . ' L';
        if ($num >= 1000) return round($num/1000, 1) . 'K';
        return (string)$num;
    }
}

if (!function_exists('slugify')) {
    function slugify(string $text): string {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^\w\s-]/', '', $text);
        $text = preg_replace('/[\s_-]+/', '-', $text);
        return trim($text, '-');
    }
}
