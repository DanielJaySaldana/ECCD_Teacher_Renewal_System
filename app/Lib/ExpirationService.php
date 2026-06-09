<?php declare(strict_types=1);


namespace App\Lib;

use DateTimeImmutable;

final class ExpirationService
{
    public static function runPassiveChecks(): void
    {
        $now = time();
        $last = (int)($_SESSION['_exp_checks_at'] ?? 0);
        if ($now - $last < 60) {
            return;
        }
        $_SESSION['_exp_checks_at'] = $now;

        try {
            self::generateExpirationNotifications(30);
        } catch (\Throwable $e) {
            error_log('ExpirationService passive check skipped: ' . $e->getMessage());
        }
    }

    public static function generateExpirationNotifications(int $daysAhead): void
    {
        $pdo = Db::pdo();
        $today = new DateTimeImmutable('today');
        $cutoff = $today->modify('+' . $daysAhead . ' days');

        $stmt = $pdo->prepare('
            SELECT t.user_id, t.expiration_date
            FROM teachers t
            WHERE t.expiration_date IS NOT NULL
              AND t.expiration_date BETWEEN ? AND ?
        ');
        $stmt->execute([$today->format('Y-m-d'), $cutoff->format('Y-m-d')]);
        $teachers = $stmt->fetchAll();

        foreach ($teachers as $t) {
            $userId = (int)$t['user_id'];
            $exp = (string)$t['expiration_date'];
            $meta = json_encode(['expiration_date' => $exp], JSON_UNESCAPED_SLASHES);

            $exists = $pdo->prepare('SELECT id FROM notifications WHERE user_id = ? AND type = ? AND meta_json LIKE ? LIMIT 1');
            $exists->execute([$userId, 'expiration_soon', '%"expiration_date":"' . $exp . '"%']);
            if ($exists->fetch()) {
                continue;
            }

            Notifier::notify(
                $userId,
                'expiration_soon',
                'Renewal Expiration Reminder',
                'Your renewal expiration date is near (' . $exp . '). Please submit your renewal requirements.',
                '/teacher/apply',
                $meta
            );
        }
    }
}