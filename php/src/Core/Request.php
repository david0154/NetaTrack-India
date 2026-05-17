<?php
namespace NetaTrack\Core;

class Request
{
    private static ?Request $instance = null;

    public static function getInstance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->post($key) ?? $this->get($key) ?? $default;
    }

    public function all(): array
    {
        $json = $this->json();
        return array_merge($_GET, $_POST, $json);
    }

    public function json(): array
    {
        $body = file_get_contents('php://input');
        if (!empty($body)) {
            $decoded = json_decode($body, true);
            return is_array($decoded) ? $decoded : [];
        }
        return [];
    }

    public function file(string $key): array|null
    {
        return $_FILES[$key] ?? null;
    }

    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    public function isMethod(string $method): bool
    {
        return $this->method() === strtoupper($method);
    }

    public function uri(): string
    {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    public function ip(): string
    {
        return $_SERVER['HTTP_CF_CONNECTING_IP']
            ?? $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['REMOTE_ADDR']
            ?? '0.0.0.0';
    }

    public function header(string $key): ?string
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $_SERVER[$key] ?? null;
    }

    public function bearerToken(): ?string
    {
        $auth = $this->header('Authorization') ?? $this->header('Authorization');
        if ($auth && str_starts_with($auth, 'Bearer ')) {
            return substr($auth, 7);
        }
        return null;
    }

    public function isAjax(): bool
    {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }

    public function sanitize(string $input): string
    {
        return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
    }

    public function validate(array $rules): array
    {
        $errors = [];
        $data = $this->all();

        foreach ($rules as $field => $rule) {
            $ruleList = explode('|', $rule);
            $value = $data[$field] ?? null;

            foreach ($ruleList as $r) {
                if ($r === 'required' && empty($value)) {
                    $errors[$field][] = "$field is required";
                } elseif (str_starts_with($r, 'max:') && strlen((string)$value) > (int)substr($r, 4)) {
                    $errors[$field][] = "$field exceeds maximum length";
                } elseif (str_starts_with($r, 'min:') && strlen((string)$value) < (int)substr($r, 4)) {
                    $errors[$field][] = "$field is too short";
                } elseif ($r === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = "$field must be a valid email";
                } elseif ($r === 'numeric' && !is_numeric($value)) {
                    $errors[$field][] = "$field must be numeric";
                } elseif ($r === 'url' && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $errors[$field][] = "$field must be a valid URL";
                }
            }
        }

        return $errors;
    }
}
