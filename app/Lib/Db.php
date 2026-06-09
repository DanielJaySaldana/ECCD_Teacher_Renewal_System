<?php declare(strict_types=1);


namespace App\Lib;

use PDO;
use PDOException;

final class Db
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo) {
            return self::$pdo;
        }

        $host = \env('DB_HOST', '127.0.0.1');
        $port = \env('DB_PORT', '3306');
        $name = \env('DB_NAME', 'renewal_system_for_child_development_teachers_database');
        $user = \env('DB_USER', 'root');
        $pass = \env('DB_PASS', '');

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

        try {
            self::$pdo = new PDO($dsn, (string)$user, (string)$pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo "Database connection failed. Check your .env settings.";
            exit;
        }

        return self::$pdo;
    }
}

