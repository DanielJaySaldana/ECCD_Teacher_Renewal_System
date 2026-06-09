<?php declare(strict_types=1);


namespace App\Lib;

use DateTimeImmutable;

final class Auth
{
    public static function check(): bool
    {
        return !empty($_SESSION['user']);
    }

    /** @return array{id:int,email:string,role:string,full_name:?string} */
    public static function user(): array
    {
        /** @var array{id:int,email:string,role:string,full_name:?string} $u */
        $u = $_SESSION['user'] ?? ['id' => 0, 'email' => '', 'role' => '', 'full_name' => null];
        return $u;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            Router::redirect('/login');
        }
    }

    public static function requireRole(string $role): void
    {
        self::requireLogin();
        if ((self::user()['role'] ?? '') !== $role) {
            http_response_code(403);
            echo "403 Forbidden";
            exit;
        }
    }

    public static function attempt(string $email, string $password): bool
    {
        $pdo = Db::pdo();

        try {
            $stmt = $pdo->prepare('SELECT u.id, u.email, u.password_hash, u.role, COALESCE(t.full_name, f.full_name) AS full_name
                                   FROM users u
                                   LEFT JOIN teachers t ON t.user_id = u.id
                                   LEFT JOIN focal f ON f.user_id = u.id
                                   WHERE u.email = ? LIMIT 1');
            $stmt->execute([$email]);
            $row = $stmt->fetch();
        } catch (\Throwable $e) {
            error_log('Auth::attempt teacher join skipped: ' . $e->getMessage());
            $stmt = $pdo->prepare('SELECT id, email, password_hash, role FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $row = $stmt->fetch();
            if (is_array($row) && !array_key_exists('full_name', $row)) {
                $row['full_name'] = null;
            }
        }

        if (!$row || empty($row['password_hash']) || !password_verify($password, (string)$row['password_hash'])) {
            return false;
        }

        $_SESSION['user'] = [
            'id' => (int)$row['id'],
            'email' => (string)$row['email'],
            'role' => (string)$row['role'],
            'full_name' => $row['full_name'] !== null ? (string)$row['full_name'] : null,
        ];

        $pdo->prepare('UPDATE users SET last_login_at = ? WHERE id = ?')
            ->execute([(new DateTimeImmutable())->format('Y-m-d H:i:s'), (int)$row['id']]);

        return true;
    }

    public static function logout(): void
    {
        unset($_SESSION['user']);
        session_regenerate_id(true);
    }
}
