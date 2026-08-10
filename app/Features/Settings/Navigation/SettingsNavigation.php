<?php

declare(strict_types=1);

namespace Flex\Features\Settings\Navigation;

use Flex\Features\AdminUI\Navigation\NavigationItem;
use Flex\Features\AdminUI\Navigation\SidebarRegistry;
use LogicException;

final class SettingsNavigation
{
    private const ITEM_ID = 'settings';

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
                    'The settings navigation item could not be resolved.'
                );
        }

        $item = NavigationItem::make(
            self::ITEM_ID,
            'Настройки',
            '/admin/settings/general'
        )
            ->icon('fa-solid fa-gear')
            ->priority(20)
            ->turbo(true)
            ->children([
                NavigationItem::make(
                    'settings.general',
                    'Основни',
                    '/admin/settings/general'
                )
                    ->icon(
                        'fa-solid fa-sliders'
                    )
                    ->priority(10)
                    ->exact(true)
                    ->turbo(true),

                NavigationItem::make(
                    'settings.mail',
                    'Поща',
                    '/admin/settings/mail'
                )
                    ->icon(
                        'fa-solid fa-envelope'
                    )
                    ->priority(20)
                    ->exact(true)
                    ->turbo(true),

                NavigationItem::make(
                    'settings.media',
                    'Медия',
                    '/admin/settings/media'
                )
                    ->icon(
                        'fa-solid fa-photo-film'
                    )
                    ->priority(30)
                    ->exact(true)
                    ->turbo(true),
            ]);

        $this->registry->add($item);

        return $item;
    }
}
