<?php declare(strict_types=1);

function env(string $key, ?string $default = null): ?string
{
    static $loaded = false;
    if (!$loaded) {
        $loaded = true;
        $envPath = __DIR__ . '/../.env';
        if (is_file($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                    continue;
                }
                [$k, $v] = explode('=', $line, 2);
                $k = trim($k);
                $v = trim($v);
                $v = trim($v, "\"'");
                $_ENV[$k] = $v;
            }
        }
    }
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

function base_url(): string
{
    $scriptName = (string)($_SERVER['SCRIPT_NAME'] ?? '');
    $dir = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
    if ($dir === '/' || $dir === '.') {
        return '';
    }
    return $dir;
}

function url(string $path): string
{
    $path = (string)$path;
    if ($path === '') {
        return base_url() ?: '/';
    }
    if (preg_match('~^https?://~i', $path)) {
        return $path;
    }
    $path = '/' . ltrim($path, '/');
    $base = base_url();
    return $base . $path;
}

const APP_NAME = 'ECCD Teacher Renewal System';
