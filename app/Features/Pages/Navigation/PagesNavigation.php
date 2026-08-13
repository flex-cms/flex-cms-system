<?php

declare(strict_types=1);

namespace Flex\Features\Pages\Navigation;

use Flex\Features\AdminUI\Navigation\NavigationItem;
use Flex\Features\AdminUI\Navigation\SidebarRegistry;
use LogicException;

final readonly class PagesNavigation
{
    private const ITEM_ID = 'pages';

    public function __construct(
        private SidebarRegistry $registry
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
                    'The pages navigation item could not be resolved.'
                );
        }

        $item = NavigationItem::make(
            self::ITEM_ID,
            'Страници',
            '/admin/pages'
        )
            ->icon('fa-solid fa-file-lines')
            ->priority(10)
            ->exact(true)
            ->turbo(true);

        $this->registry->add(
            $item,
            SidebarRegistry::DEFAULT_SIDEBAR
        );

        return $item;
    }
}
