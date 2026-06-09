<?php
declare(strict_types=1);


require __DIR__ . '/app/bootstrap.php';

use App\Lib\Auth;
use App\Lib\ExpirationService;
use App\Lib\Router;

ExpirationService::runPassiveChecks();

$router = new Router();
require __DIR__ . '/app/routes.php';

$router->get('/', function (): void {
    if (!Auth::check()) {
        Router::redirect('/login');
    }
    $role = Auth::user()['role'] ?? '';
    Router::redirect($role === 'focal' ? '/focal' : '/teacher');
});

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');


