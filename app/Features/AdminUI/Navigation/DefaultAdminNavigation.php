<?php

declare(strict_types=1);

namespace Flex\Features\AdminUI\Navigation;

final class DefaultAdminNavigation
{
    public function __construct(
        private readonly SidebarRegistry $registry
    ) {
    }

    public function register(): SidebarDefinition
    {
        if ($this->registry->has(
            SidebarRegistry::DEFAULT_SIDEBAR
        )) {
            return $this->registry->sidebar(
                SidebarRegistry::DEFAULT_SIDEBAR
            );
        }

        return $this->registry
            ->create(
                SidebarRegistry::DEFAULT_SIDEBAR,
                'Основна навигация',
                SidebarPosition::Left
            )
            ->priority(10)
            ->collapsible(true);
    }
}
