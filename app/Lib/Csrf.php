<?php declare(strict_types=1);


namespace App\Lib;

final class Csrf
{
    public static function ensureToken(): void
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
    }

    public static function token(): string
    {
        self::ensureToken();
        return (string)($_SESSION['_csrf'] ?? '');
    }

    public static function validate(?string $token): bool
    {
        $sessionToken = (string)($_SESSION['_csrf'] ?? '');
        return $token !== null && $sessionToken !== '' && hash_equals($sessionToken, $token);
    }
}

