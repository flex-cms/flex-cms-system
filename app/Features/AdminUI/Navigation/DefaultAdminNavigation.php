<?php

declare(strict_types=1);

namespace Flex\Features\AdminUI\Navigation;

use Flex\Features\Settings\Models\Setting;

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

        $position = $this->resolveSidebarPosition();

        return $this->registry
            ->create(
                SidebarRegistry::DEFAULT_SIDEBAR,
                'Основна навигация',
                $position
            )
            ->priority(10)
            ->collapsible(true);
    }

    private function resolveSidebarPosition(): SidebarPosition
    {
        $setting = Setting::query()
            ->where('key', 'admin_sidebar_position')
            ->first();

        return SidebarPosition::resolve(
            $setting?->typedValue(),
            SidebarPosition::Left
        );
    }
}
