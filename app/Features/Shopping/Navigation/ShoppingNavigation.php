<?php

declare(strict_types=1);

namespace Flex\Features\Shopping\Navigation;

use Flex\Features\AdminUI\Navigation\NavigationItem;
use Flex\Features\AdminUI\Navigation\SidebarRegistry;
use LogicException;

final class ShoppingNavigation
{
    private const ITEM_ID = 'shopping';

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
                    'The shopping navigation item could not be resolved.'
                );
        }

        $item = NavigationItem::make(
            self::ITEM_ID,
            'Магазин',
            '/admin/shopping/categories'
        )
            ->icon('fa-solid fa-cart-shopping')
            ->priority(30)
            ->turbo(true)
            ->children([
                NavigationItem::make(
                    'shopping.categories',
                    'Категории',
                    '/admin/shopping/categories'
                )
                    ->icon('fa-solid fa-layer-group')
                    ->priority(10)
                    ->exact(true)
                    ->turbo(true),

                NavigationItem::make(
                    'shopping.products',
                    'Продукти',
                    '/admin/shopping/products'
                )
                    ->icon('fa-solid fa-box')
                    ->priority(20)
                    ->turbo(true),

                NavigationItem::make(
                    'shopping.orders',
                    'Поръчки',
                    '/admin/shopping/orders'
                )
                    ->icon('fa-solid fa-receipt')
                    ->priority(30)
                    ->turbo(true),
            ]);

        $this->registry->add($item);

        return $item;
    }
}
