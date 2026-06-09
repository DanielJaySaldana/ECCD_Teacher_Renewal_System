<?php declare(strict_types=1);


namespace App\Lib;

final class Router
{
    /** @var array<string, array<string, callable>> */
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$this->normalize($path)] = $handler;
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes['POST'][$this->normalize($path)] = $handler;
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        if (!isset($this->routes[$method])) {
            http_response_code(405);
            echo '405 Method Not Allowed';
            return;
        }

        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = $this->normalize($this->stripBasePath($path));

        $handler = $this->routes[$method][$path] ?? null;
        if (!$handler) {
            http_response_code(404);
            echo '404 Not Found';
            return;
        }

        call_user_func($handler);
    }

    public static function redirect(string $to): void
    {
        $target = $to;
        if (str_starts_with($to, '/')) {
            $target = \url($to);
        }
        header('Location: ' . $target);
        exit;
    }

    private function normalize(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        return rtrim($path, '/') ?: '/';
    }

    private function stripBasePath(string $path): string
    {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $baseDir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
        if ($baseDir === '' || $baseDir === '/') {
            return $path;
        }
        if (str_starts_with($path, $baseDir . '/')) {
            return substr($path, strlen($baseDir));
        }
        return $path;
    }
}
