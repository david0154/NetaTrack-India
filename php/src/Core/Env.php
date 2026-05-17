<?php
namespace NetaTrack\Core;

class Env {
    public static function load(string $path): void {
        if (!file_exists($path)) return;
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) continue;
            [$key, $val] = array_map('trim', explode('=', $line, 2));
            if (!empty($key)) {
                $val = trim($val, '"\' ');
                putenv("$key=$val");
                $_ENV[$key] = $val;
                $_SERVER[$key] = $val;
            }
        }
    }

    public static function get(string $key, mixed $default = null): mixed {
        return getenv($key) ?: $_ENV[$key] ?? $default;
    }
}
