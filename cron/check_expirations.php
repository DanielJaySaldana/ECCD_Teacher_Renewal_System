<?php declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

\App\Lib\ExpirationService::generateExpirationNotifications(30);

echo "OK\n";
