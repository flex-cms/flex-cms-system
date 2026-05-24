<?php

namespace Flex\Core;

use Flex\Models\User;

class Auth
{
    private const DURATION_DEFAULT = 3600;
    private const DURATION_HOUR = 3600;
    private const DURATION_MONTH = 30 * 24 * 60 * 60;

    protected static ?User $currentUser = null;

    public static function attempt(string $usernameOrEmail, string $password, bool $remember = false, string $rememberDuration = 'month'): bool
    {
        $user = User::where('email', $usernameOrEmail)->orWhere('username', $usernameOrEmail)->first();

        if ($user && $user->is_active && password_verify($password, $user->password)) {

            self::clearRememberCookie();
            self::login($user);

            if ($remember) {
                $seconds = ($rememberDuration === 'hour') ? self::DURATION_HOUR : self::DURATION_MONTH;
            } else {
                $seconds = self::DURATION_DEFAULT;
            }

            $expireTime = time() + $seconds;
            self::setRememberToken($user, $expireTime);

            return true;
        }

        return false;
    }

    public static function login(User $user): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['user_id'] = $user->id;
        $_SESSION['is_admin'] = $user->hasRole('admin');
        $_SESSION['permissions'] = $user->getPermissions();

        self::$currentUser = $user;

        $user->update(['last_login' => date('Y-m-d H:i:s')]);
    }

    public static function check(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_COOKIE['remember_token']) || empty($_COOKIE['remember_token'])) {
            if (isset($_SESSION['user_id'])) {
                self::logout();
            }
            return false;
        }

        if (isset($_SESSION['user_id'])) {
            if (self::$currentUser !== null) {
                return true;
            }

            $user = User::find($_SESSION['user_id']);
            if ($user && $user->is_active) {
                self::$currentUser = $user;
                return true;
            }

            self::logout();
            return false;
        }

        $token = $_COOKIE['remember_token'];
        $user = User::where('remember_token', $token)->first();

        if ($user && $user->is_active) {
            self::login($user);
            return true;
        }

        self::logout();
        return false;
    }

    public static function isAdmin(): bool
    {
        return self::check() && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
    }

    public static function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['user_id'])) {
            $user = User::find($_SESSION['user_id']);
            if ($user) {
                $user->update(['remember_token' => null]);
            }
        }

        self::clearRememberCookie();
        self::$currentUser = null;

        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function user(): ?User
    {
        if (self::$currentUser !== null) {
            return self::$currentUser;
        }

        if (self::check()) {
            if (isset($_SESSION['user_id'])) {
                self::$currentUser = User::find($_SESSION['user_id']);
                return self::$currentUser;
            }
        }

        return null;
    }

    private static function setRememberToken(User $user, int $expireTime): void
    {
        $token = bin2hex(random_bytes(32));

        $user->update(['remember_token' => $token]);
        $_COOKIE['remember_token'] = $token;

        setcookie(
            'remember_token',
            $token,
            [
                'expires' => $expireTime,
                'path' => '/',
                'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443),
                'httponly' => true,
                'samesite' => 'Lax'
            ]
        );
    }

    private static function clearRememberCookie(): void
    {
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
            unset($_COOKIE['remember_token']);
        }
    }
}