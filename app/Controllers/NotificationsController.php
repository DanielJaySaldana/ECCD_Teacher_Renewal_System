<?php declare(strict_types=1);


namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\Csrf;
use App\Lib\Db;
use App\Lib\Router;
use App\Lib\View;

final class NotificationsController
{
    public static function index(): void
    {
        Auth::requireLogin();
        $pdo = Db::pdo();
        $stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 100');
        $stmt->execute([Auth::user()['id']]);
        $items = $stmt->fetchAll();

        View::render('notifications/index', ['title' => 'Notifications', 'items' => $items]);
    }

    public static function markRead(): void
    {
        Auth::requireLogin();
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(400);
            echo 'Bad Request (CSRF)';
            return;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            Router::redirect('/notifications');
        }

        $pdo = Db::pdo();
        $pdo->prepare('UPDATE notifications SET read_at = NOW() WHERE id = ? AND user_id = ?')
            ->execute([$id, Auth::user()['id']]);

        Router::redirect('/notifications');
    }

    public static function clear(): void
    {
        Auth::requireLogin();
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(400);
            echo 'Bad Request (CSRF)';
            return;
        }

        $pdo = Db::pdo();
        $pdo->prepare('DELETE FROM notifications WHERE user_id = ?')
            ->execute([Auth::user()['id']]);

        Router::redirect('/notifications');
    }
}
