<?php

namespace Flex\Core\Controllers;

use Flex\Attributes\UseExceptions;
use Flex\Core\Auth;
use Flex\Core\Controllers\BaseController;
use Flex\Core\Helpers\Flash;
use Flex\Core\Routing\View;

class AuthController extends BaseController
{
    #[UseExceptions]
    public function login(): void
    {
        if (Auth::check()) {
            $this->redirectByUserRole();
            return;
        }

        render_view('auth/login', [], 'core', 'main');
    }

    #[UseExceptions]
    public function authenticate(): void
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        $duration = $_POST['remember_duration'] ?? 'month';

        if (Auth::attempt($email, $password, $remember, $duration)) {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            $redirectUrl = $_SESSION['redirect_url'] ?? null;
            if ($redirectUrl) {
                unset($_SESSION['redirect_url']);
                View::redirect($redirectUrl, 302);
            }

            $this->redirectByUserRole();
        }

        $data = [
            'error' => 'Невалиден имейл адрес, парола или неактивен профил!',
            'old' => ['email' => $email],
        ];

        render_view('auth/login', $data, 'core', 'main');
    }

    #[UseExceptions]
    public function logout(): void
    {
        Auth::logout();
        View::redirect('/admin');
    }

    #[UseExceptions]
    private function redirectByUserRole(): void
    {
        if (Auth::isAdmin()) {
            View::redirect('/admin/dashboard');
        } else {
            View::redirect('/');
        }
    }
}
