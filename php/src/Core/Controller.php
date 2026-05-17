<?php
namespace NetaTrack\Core;

class Controller {
    protected function view(string $view, array $data = [], string $layout = 'main'): void {
        extract($data);
        $viewFile = APP_PATH . '/views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: $view");
        }
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        $layoutFile = APP_PATH . "/views/layouts/$layout.php";
        if (file_exists($layoutFile)) {
            require $layoutFile;
        } else {
            echo $content;
        }
    }

    protected function json(mixed $data, int $code = 200): void {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    protected function redirect(string $url): void {
        header("Location: $url");
        exit;
    }

    protected function back(): void {
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }

    protected function isPost(): bool { return $_SERVER['REQUEST_METHOD'] === 'POST'; }
    protected function isGet(): bool  { return $_SERVER['REQUEST_METHOD'] === 'GET';  }

    protected function input(string $key, mixed $default = null): mixed {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function sanitize(string $val): string {
        return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
    }

    protected function csrf(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function verifyCsrf(): bool {
        $token = $_POST['_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }

    protected function requireAuth(): void {
        if (empty($_SESSION['user_id'])) {
            $this->redirect('/login');
        }
    }

    protected function requireAdmin(): void {
        if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
            $this->redirect('/login');
        }
    }

    protected function paginate(string $sql, array $params = [], int $perPage = 20): array {
        $page  = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $perPage;
        $countSql = "SELECT COUNT(*) as total FROM (" . $sql . ") as sub";
        $total = (int)(Database::fetch($countSql, $params)['total'] ?? 0);
        $items = Database::fetchAll("$sql LIMIT $perPage OFFSET $offset", $params);
        return [
            'items'       => $items,
            'total'       => $total,
            'per_page'    => $perPage,
            'current_page'=> $page,
            'last_page'   => (int)ceil($total / $perPage),
        ];
    }
}
