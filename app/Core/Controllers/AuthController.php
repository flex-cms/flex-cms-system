<?php

namespace Flex\Core\Controllers;

use Flex\Attributes\UseExceptions;
use Flex\Core\Auth;
use Flex\Core\Controllers\BaseController;
use Flex\Core\Routing\View;

class AuthController extends BaseController
{
    #[UseExceptions]
    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirectByUserRole();
            return;
        }

        render_view('auth/login', [], 'core', 'main');
    }

    #[UseExceptions]
    public function login(): void
    {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (Auth::attempt($username, $password)) {
            $this->redirectByUserRole();
            return;
        }

        $data = [
            'error' => 'Невалидно потребителско име или парола!',
            'old' => ['username' => $username]
        ];

        render_view('auth/login', $data);
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
