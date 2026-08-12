<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Support;

final class AuthenticationTables
{
    public const PREFIX = 'authentication_';

    public static function users(): string
    {
        return self::PREFIX . 'users';
    }

    public static function roles(): string
    {
        return self::PREFIX . 'roles';
    }

    public static function permissions(): string
    {
        return self::PREFIX . 'permissions';
    }

    public static function rolePermission(): string
    {
        return self::PREFIX . 'role_permission';
    }

    public static function userRole(): string
    {
        return self::PREFIX . 'user_role';
    }

    public static function superAdministratorIndex(): string
    {
        return self::PREFIX . 'users_single_super_admin_unique';
    }

    private function __construct()
    {
    }
}
