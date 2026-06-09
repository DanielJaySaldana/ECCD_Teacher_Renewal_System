<?php declare(strict_types=1);

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\Csrf;
use App\Lib\Flash;
use App\Lib\Router;
use App\Lib\Validator;
use App\Lib\View;

final class AuthController
{
    public static function showLogin(): void
    {
        if (Auth::check()) {
            Router::redirect('/');
        }
        View::render('auth/login', ['title' => 'Login']);
    }

    public static function login(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(400);
            echo 'Bad Request (CSRF)';
            return;
        }

        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if (!Validator::email($email) || $password === '') {
            Flash::set('danger', 'Enter a valid email and password.');
            Router::redirect('/login');
        }

        if (!Auth::attempt($email, $password)) {
            Flash::set('danger', 'Invalid login.');
            Router::redirect('/login');
        }

        Router::redirect('/');
    }

    public static function logout(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(400);
            echo 'Bad Request (CSRF)';
            return;
        }
        Auth::logout();
        Router::redirect('/login');
    }
}
