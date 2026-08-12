<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Providers;

use Flex\Core\Container\Container;
use Flex\Core\Features\Contracts\FeatureServiceProviderInterface;
use Flex\Features\Authentication\Navigation\AuthenticationNavigation;
use Flex\Features\Authentication\Repositories\EloquentRoleRepository;
use Flex\Features\Authentication\Repositories\EloquentUserRepository;
use Flex\Features\Authentication\Repositories\RoleRepositoryInterface;
use Flex\Features\Authentication\Repositories\UserRepositoryInterface;
use Flex\Features\Authentication\Services\AuthorizationService;
use Flex\Features\Authentication\Services\PermissionRegistry;
use Flex\Features\Authentication\Services\RoleService;
use Flex\Features\Authentication\Services\UserService;

final class AuthenticationServiceProvider implements FeatureServiceProviderInterface
{
    public function register(Container $container): void
    {
        $container->singleton(UserRepositoryInterface::class, EloquentUserRepository::class);
        $container->singleton(RoleRepositoryInterface::class, EloquentRoleRepository::class);
        $container->singleton(AuthorizationService::class);
        $container->singleton(UserService::class);
        $container->singleton(RoleService::class);
        $container->singleton(PermissionRegistry::class);
        $container->singleton(AuthenticationNavigation::class);

        $container->make(PermissionRegistry::class)->sync();
        $container->make(AuthenticationNavigation::class)->register();
    }
}
