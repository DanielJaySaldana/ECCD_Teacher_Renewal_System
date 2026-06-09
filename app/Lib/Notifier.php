<?php declare(strict_types=1);


namespace App\Lib;

use DateTimeImmutable;

final class Notifier
{
    public static function notify(int $userId, string $type, string $title, string $body, ?string $linkUrl = null, ?string $metaJson = null): void
    {
        $pdo = Db::pdo();
        $pdo->prepare('INSERT INTO notifications (user_id, type, title, body, link_url, meta_json, created_at)
                       VALUES (?, ?, ?, ?, ?, ?, ?)')
            ->execute([
                $userId,
                $type,
                $title,
                $body,
                $linkUrl,
                $metaJson,
                (new DateTimeImmutable())->format('Y-m-d H:i:s'),
            ]);
    }
}
