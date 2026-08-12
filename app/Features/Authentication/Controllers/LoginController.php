<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Controllers;

use Flex\Core\Http\RedirectResponse;
use Flex\Core\Http\Request;
use Flex\Core\View\Contracts\ViewRendererInterface;
use Flex\Core\View\ViewResponse;
use Flex\Features\Authentication\Services\AuthenticationService;
use Flex\Features\Authentication\Providers\AuthProvider;

final readonly class LoginController
{
    private const MAX_ATTEMPTS = 5;
    private const LOCK_SECONDS = 60;

    public function __construct(
        private AuthenticationService $authentication,
        private ViewRendererInterface $views
    ) {
    }

    public function show(): ViewResponse|RedirectResponse
    {
        if (AuthProvider::check()) {
            return new RedirectResponse('/admin/dashboard');
        }

        return $this->loginView();
    }

    public function login(Request $request): ViewResponse|RedirectResponse
    {
        if (!$this->validCsrfToken($request->string('_token'))) {
            return $this->loginView('Сесията изтече. Моля, опитайте отново.', '', 419);
        }

        if ($this->isRateLimited()) {
            return $this->loginView(
                'Твърде много неуспешни опити. Опитайте отново след една минута.',
                $request->string('email'),
                429
            );
        }

        $email = strtolower(trim($request->string('email')));
        $authenticated = $this->authentication->attemptAdministrator(
            $email,
            $request->string('password'),
            $request->boolean('remember')
        );

        if (!$authenticated) {
            $this->recordFailure();

            return $this->loginView(
                'Невалиден имейл, парола или липсва административен достъп.',
                $email,
                422
            );
        }

        unset($_SESSION['admin_login_attempts'], $_SESSION['admin_login_locked_until']);

        $redirect = $_SESSION['redirect_url'] ?? '/admin/dashboard';
        unset($_SESSION['redirect_url']);

        if (!is_string($redirect) || !str_starts_with($redirect, '/admin')) {
            $redirect = '/admin/dashboard';
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        return new RedirectResponse($redirect);
    }

    public function logout(Request $request): RedirectResponse
    {
        if (!$this->validCsrfToken($request->string('_token'))) {
            return new RedirectResponse('/admin/dashboard');
        }

        $this->authentication->logout();

        return new RedirectResponse('/login');
    }

    private function loginView(
        ?string $error = null,
        string $email = '',
        int $status = 200
    ): ViewResponse {
        return $this->views->response(
            'Authentication::login',
            [
                'title' => 'Административен вход',
                'error' => $error,
                'email' => $email,
                'csrfToken' => $this->csrfToken(),
            ],
            status: $status
        );
    }

    private function csrfToken(): string
    {
        if (!isset($_SESSION['authentication_csrf'])) {
            $_SESSION['authentication_csrf'] = bin2hex(random_bytes(32));
        }

        return (string) $_SESSION['authentication_csrf'];
    }

    private function validCsrfToken(string $token): bool
    {
        $expected = $_SESSION['authentication_csrf'] ?? null;

        return is_string($expected) && $token !== '' && hash_equals($expected, $token);
    }

    private function isRateLimited(): bool
    {
        return (int) ($_SESSION['admin_login_locked_until'] ?? 0) > time();
    }

    private function recordFailure(): void
    {
        $attempts = (int) ($_SESSION['admin_login_attempts'] ?? 0) + 1;
        $_SESSION['admin_login_attempts'] = $attempts;

        if ($attempts >= self::MAX_ATTEMPTS) {
            $_SESSION['admin_login_attempts'] = 0;
            $_SESSION['admin_login_locked_until'] = time() + self::LOCK_SECONDS;
        }
    }
}
