<?php
namespace NetaTrack\Core;

/**
 * NetaTrack India - Base Controller
 */
abstract class Controller
{
    protected function view(string $view, array $data = [], ?string $layout = 'layouts/main'): void
    {
        extract($data);
        ob_start();
        $viewFile = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: {$view}");
        }
        require $viewFile;
        $content = ob_get_clean();

        if ($layout) {
            $layoutFile = VIEWS_PATH . '/' . str_replace('.', '/', $layout) . '.php';
            if (file_exists($layoutFile)) {
                require $layoutFile;
                return;
            }
        }
        echo $content;
    }

    protected function adminView(string $view, array $data = []): void
    {
        $this->view('admin/' . $view, $data, 'layouts/admin');
    }

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    protected function redirect(string $url, int $status = 302): void
    {
        http_response_code($status);
        header("Location: {$url}");
        exit;
    }

    protected function back(): void
    {
        $ref = $_SERVER['HTTP_REFERER'] ?? url('/');
        $this->redirect($ref);
    }

    protected function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function inputAll(): array
    {
        return array_merge($_GET ?? [], $_POST ?? []);
    }

    protected function file(string $key): ?array
    {
        return isset($_FILES[$key]) && $_FILES[$key]['error'] === UPLOAD_ERR_OK
            ? $_FILES[$key] : null;
    }

    protected function validate(array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $rule) {
            $value = $this->input($field);
            foreach (explode('|', $rule) as $r) {
                if ($r === 'required' && empty($value)) {
                    $errors[$field] = ucfirst($field) . ' is required.';
                } elseif (str_starts_with($r, 'min:') && strlen((string)$value) < (int)substr($r, 4)) {
                    $errors[$field] = ucfirst($field) . ' must be at least ' . substr($r, 4) . ' characters.';
                } elseif (str_starts_with($r, 'max:') && strlen((string)$value) > (int)substr($r, 4)) {
                    $errors[$field] = ucfirst($field) . ' must not exceed ' . substr($r, 4) . ' characters.';
                } elseif ($r === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = ucfirst($field) . ' must be a valid email.';
                }
            }
        }
        return $errors;
    }

    protected function abort(int $code = 404): void
    {
        http_response_code($code);
        $view = VIEWS_PATH . '/errors/' . $code . '.php';
        if (file_exists($view)) require $view;
        else echo "Error {$code}";
        exit;
    }

    protected function csrf(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function verifyCsrf(): void
    {
        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            $this->abort(419);
        }
    }

    protected function isPost(): bool { return $_SERVER['REQUEST_METHOD'] === 'POST'; }
    protected function isGet():  bool { return $_SERVER['REQUEST_METHOD'] === 'GET';  }
    protected function isAjax(): bool { return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest'; }
}
