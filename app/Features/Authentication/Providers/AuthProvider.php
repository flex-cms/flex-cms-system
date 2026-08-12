<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Providers;

use Flex\Features\Authentication\Models\User;
use Flex\Features\Authentication\Support\AuthenticationTables;
use Illuminate\Support\Collection;

final class AuthProvider
{
    private const DURATION_HOUR = 3600;
    private const DURATION_MONTH = 30 * 24 * 60 * 60;
    private const REMEMBER_COOKIE = 'remember_token';

    private static ?User $currentUser = null;

    public static function attempt(
        string $email,
        string $password,
        bool $remember = false,
        string $rememberDuration = 'month'
    ): bool {
        $email = strtolower(trim($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
            return false;
        }

        $user = User::query()
            ->where('email', $email)
            ->first();

        if (
            $user === null
            || !$user->is_active
            || !password_verify($password, (string) $user->password)
        ) {
            return false;
        }

        self::clearRememberCookie();

        self::login($user);

        if ($remember) {
            self::setRememberToken(
                $user,
                time() + self::rememberDurationInSeconds($rememberDuration)
            );
        } else {
            $user->forceFill(['remember_token' => null])->save();
        }

        return true;
    }

    public static function login(User $user): void
    {
        if (!$user->is_active) {
            return;
        }

        self::$currentUser = $user;

        $_SESSION['user_id'] = (int) $user->id;
        $_SESSION['is_admin'] = self::canAccessAdministration($user);
        $_SESSION['permissions'] = self::permissionSlugs($user);

        $user->forceFill([
            'last_login' => date('Y-m-d H:i:s'),
        ])->save();
    }

    public static function check(): bool
    {
        if (self::$currentUser !== null) {
            return self::$currentUser->is_active;
        }

        if (isset($_SESSION['user_id'])) {
            $user = User::query()->find((int) $_SESSION['user_id']);

            if ($user !== null && $user->is_active) {
                self::$currentUser = $user;

                return true;
            }


            self::logout();

            return false;
        }

        $token = $_COOKIE[self::REMEMBER_COOKIE] ?? null;

        if (!is_string($token) || $token === '') {
            return false;
        }

        $user = User::query()
            ->where('remember_token', $token)
            ->first();

        if ($user === null || !$user->is_active) {
            self::clearRememberCookie();

            return false;
        }

        self::login($user);

        return true;
    }

    public static function guest(): bool
    {
        return !self::check();
    }

    public static function isAdmin(): bool
    {
        $user = self::user();

        return $user !== null
            && self::canAccessAdministration($user);
    }

    public static function hasPermission(string $permission): bool
    {
        $user = self::user();

        return $user !== null
            && $user->hasPermission($permission);
    }

    public static function user(): ?User
    {
        if (self::$currentUser !== null) {
            return self::$currentUser;
        }

        if (!self::check()) {
            return null;
        }

        return self::$currentUser;
    }

    public static function id(): ?int
    {
        $user = self::user();

        return $user !== null
            ? (int) $user->id
            : null;
    }

    public static function logout(): void
    {
        $user = self::$currentUser;

        if ($user === null && isset($_SESSION['user_id'])) {
            $user = User::query()->find((int) $_SESSION['user_id']);
        }

        if ($user !== null) {
            $user->forceFill(['remember_token' => null])->save();
        }

        self::clearRememberCookie();
        self::$currentUser = null;
        $_SESSION = [];

        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        if ((bool) ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                [
                    'expires' => time() - 42000,
                    'path' => $params['path'],
                    'domain' => $params['domain'],
                    'secure' => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => $params['samesite'] ?? 'Lax',
                ]
            );
        }

        session_destroy();
    }

    private static function canAccessAdministration(User $user): bool
    {
        return $user->is_active
            && (
                $user->isSuperAdministrator()
                || $user->hasPermission('admin.access')
            );
    }

    /** @return array<int, string> */
    private static function permissionSlugs(User $user): array
    {
        if ($user->isSuperAdministrator()) {
            return ['*'];
        }

        /** @var Collection<int, string> $permissions */
        $permissions = $user->roles()
            ->where(AuthenticationTables::roles() . '.is_active', true)
            ->with([
                'permissions' => static fn($query) => $query
                    ->where(AuthenticationTables::permissions() . '.is_active', true),
            ])
            ->get()
            ->flatMap(
                static fn($role): Collection => $role->permissions
                    ->pluck('slug')
            )
            ->unique()
            ->values();

        return $permissions->all();
    }

    private static function setRememberToken(
        User $user,
        int $expiresAt
    ): void {
        $token = bin2hex(random_bytes(32));

        $user->forceFill(['remember_token' => $token])->save();

        $_COOKIE[self::REMEMBER_COOKIE] = $token;

        setcookie(
            self::REMEMBER_COOKIE,
            $token,
            [
                'expires' => $expiresAt,
                'path' => '/',
                'secure' => self::usesSecureCookies(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]
        );
    }

    private static function clearRememberCookie(): void
    {
        setcookie(
            self::REMEMBER_COOKIE,
            '',
            [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => self::usesSecureCookies(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]
        );

        unset($_COOKIE[self::REMEMBER_COOKIE]);
    }

    private static function rememberDurationInSeconds(string $duration): int
    {
        return $duration === 'hour'
            ? self::DURATION_HOUR
            : self::DURATION_MONTH;
    }

    private static function usesSecureCookies(): bool
    {
        return (
            isset($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== ''
            && $_SERVER['HTTPS'] !== 'off'
        ) || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443;
    }
}
