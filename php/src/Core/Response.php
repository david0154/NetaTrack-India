<?php
namespace NetaTrack\Core;

class Response
{
    private int    $status  = 200;
    private array  $headers = [];

    public function status(int $code): self
    {
        $this->status = $code;
        http_response_code($code);
        return $this;
    }

    public function header(string $name, string $value): self
    {
        header("$name: $value");
        return $this;
    }

    public function redirect(string $path, int $code = 302): void
    {
        $base = rtrim(config('app.url', ''), '/');
        header("Location: $base/$path", true, $code);
        exit;
    }

    public function view(string $template, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        // Capture the view content
        ob_start();
        $viewFile = BASE_PATH.'/views/'.$template.'.php';
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View not found: $template");
        }
        require $viewFile;
        $content = ob_get_clean();

        // Determine layout
        $layout = str_starts_with($template, 'admin/')  ? 'admin'  :
                  (str_starts_with($template, 'auth/')   ? 'auth'   : 'public');

        require BASE_PATH.'/views/layouts/'.$layout.'.php';
    }

    public function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public function notFound(): void
    {
        $this->status(404);
        require BASE_PATH.'/views/errors/404.php';
        exit;
    }

    public function serverError(string $message = 'Server Error'): void
    {
        $this->status(500);
        echo "<h1>500 — $message</h1>";
        exit;
    }
}
