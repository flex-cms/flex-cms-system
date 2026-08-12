<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Navigation;

use Flex\Features\AdminUI\Navigation\NavigationItem;
use Flex\Features\AdminUI\Navigation\SidebarRegistry;

final readonly class AuthenticationNavigation
{
    public function __construct(private SidebarRegistry $registry) {}
    public function register(): NavigationItem
    {
        $sidebar = $this->registry->sidebar(SidebarRegistry::DEFAULT_SIDEBAR);
        if ($existing = $sidebar->find('authentication')) { return $existing; }
        $item = NavigationItem::make('authentication', 'Достъп', '/admin/authentication/users')->icon('fa-solid fa-user-shield')->priority(15)->turbo(true)->children([
            NavigationItem::make('authentication.users', 'Потребители', '/admin/authentication/users')->icon('fa-solid fa-users')->priority(10)->exact(true)->turbo(true),
            NavigationItem::make('authentication.roles', 'Роли', '/admin/authentication/roles')->icon('fa-solid fa-id-badge')->priority(20)->exact(true)->turbo(true),
            NavigationItem::make('authentication.permissions', 'Разрешения', '/admin/authentication/permissions')->icon('fa-solid fa-key')->priority(30)->exact(true)->turbo(true),
        ]);
        $this->registry->add($item);
        return $item;
    }
}
