<?php

declare(strict_types=1);

namespace Flex\Features\Dashboard\Navigation;

use Flex\Features\AdminUI\Navigation\NavigationItem;
use Flex\Features\AdminUI\Navigation\SidebarRegistry;
use LogicException;

final class DashboardNavigation
{
    private const ITEM_ID = 'dashboard';

    public function __construct(
        private readonly SidebarRegistry $registry
    ) {
    }

    public function register(): NavigationItem
    {
        $sidebar = $this->registry->sidebar(
            SidebarRegistry::DEFAULT_SIDEBAR
        );

        if ($sidebar->has(self::ITEM_ID)) {
            return $sidebar->find(self::ITEM_ID)
                ?? throw new LogicException(
                    'The dashboard navigation item could not be resolved.'
                );
        }

        $item = NavigationItem::make(
            self::ITEM_ID,
            'Табло',
            '/admin/dashboard-preview'
        )
            ->icon(
                'fa-solid fa-gauge'
            )
            ->priority(1)
            ->exact(true)
            ->turbo(true);

        $this->registry->add(
            $item,
            SidebarRegistry::DEFAULT_SIDEBAR
        );

        return $item;
    }
}
