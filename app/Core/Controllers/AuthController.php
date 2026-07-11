<?php

namespace Flex\Core\Controllers;

use Flex\Attributes\UseExceptions;
use Flex\Core\Auth;
use Flex\Core\Controllers\BaseController;
use Flex\Core\Helpers\Flash;
use Flex\Core\Routing\View;
use Flex\Core\Services\PasswordResetService;
use Flex\Models\PasswordReset;
use Flex\Models\User;

class AuthController extends BaseController
{
    #[UseExceptions]
    public function login(): void
    {
        if (Auth::check()) {
            $this->redirectByUserRole();
            return;
        }

        render_view('auth/login', ['title' => 'Вход'], 'core', 'main');
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

        if (Auth::attempt($email, $password)) {
            $this->redirectByUserRole();
        }

        Flash::error('Невалиден имейл адрес или парола!');

        render_view('auth/login', ['old' => ['email' => $email]], 'core', 'main');
    }

    #[UseExceptions]
    public function logout(): void
    {
        Auth::logout();
        View::redirect('/login');
    }

    #[UseExceptions]
    public function showForgotPassword(): void
    {
        render_view('auth/forgot-password', ['title' => 'Забравена парола'], 'core', 'main');
    }

    #[UseExceptions]
    public function forgotPassword()
    {
        $email = $_POST['email'] ?? '';

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::error('Моля, въведете валиден имейл адрес.');
            render_view('auth/forgot-password', ['old' => ['email' => $email]], 'core', 'main');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            Flash::error('Потребител с този имейл адрес не е намерен.');
            render_view('auth/forgot-password', ['old' => ['email' => $email]], 'core', 'main');
        }

        if (!method_exists($user, 'isActive') || !$user->isActive()) {
            Flash::error('Вашият акаунт е деактивиран. Моля, свържете се с администратора.');
            render_view('auth/forgot-password', ['old' => ['email' => $email]], 'core', 'main');
        }

        try {
            $service = new PasswordResetService();
            $isSent = $service->handle($email);
        } catch (\Exception $e) {
            Flash::error($e->getMessage());
            render_view('auth/forgot-password', ['old' => ['email' => $email]], 'core', 'main');
        }

        if (!$isSent) {
            Flash::error('Не може да се изпрати линк за възстановяване на паролата. Моля, опитайте отново по-късно.');
            render_view('auth/forgot-password', ['old' => ['email' => $email]], 'core', 'main');
        }

        Flash::success('Линкът за възстановяване е изпратен успешно на Вашия имейл!');
        View::redirect('/password/forgot');
    }

    #[UseExceptions]
    public function showResetPassword(): void
    {
        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            Flash::error('Невалиден или липсващ токен за възстановяване.');
            View::redirect('/login');
        }

        $resetRecord = PasswordReset::checkToken($token);

        if (!$resetRecord) {
            Flash::error('Линкът за възстановяване е невалиден или е изтекъл.');
            View::redirect('/login');
        }

        render_view('auth/password-reset', [], 'core', 'main');
    }

    #[UseExceptions]
    public function resetPassword(): void
    {
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';

        if (empty($token) || empty($password)) {
            Flash::error('Всички полета са задължителни.');
            View::redirect("/password/reset?token=" . urlencode($token));
        }

        if ($password !== $passwordConfirmation) {
            Flash::error('Паролите не съвпадат!');
            View::redirect("/password/reset?token=" . urlencode($token));
        }

        $resetRecord = PasswordReset::checkToken($token);

        if (!$resetRecord) {
            Flash::error('Линкът за възстановяване е невалиден или е изтекъл.');
            View::redirect('/login');
        }

        $user = User::where('email', $resetRecord->email)->first();

        if (!$user) {
            Flash::error('Невалидна заявка за промяна на парола.');
            View::redirect('/login');
        }

        User::where('email', $resetRecord->email)->update([
            'password' => password_hash($password, PASSWORD_BCRYPT)
        ]);

        PasswordReset::deleteExistingForEmail($resetRecord->email);

        Flash::success('Паролата ви беше променена успешно! Може да влезете в профила си.');
        View::redirect('/login');
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
