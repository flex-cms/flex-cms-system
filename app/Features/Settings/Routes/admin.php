<?php

declare(strict_types=1);

use Flex\Core\Routing\FlexRouter;
use Flex\Features\Settings\Controllers\SettingsController;

FlexRouter::prefix('/admin/settings')
    ->name('admin.settings.')
    ->middleware(['auth', 'admin'])
    ->group(static function (): void {
        FlexRouter::get(
            '/runtime/date',
            [SettingsController::class, 'dateRuntimeConfig']
        )->name('runtime.date');

        FlexRouter::get(
            '/{group}',
            [SettingsController::class, 'show']
        )
            ->whereIn('group', [
                'general',
                'mail',
                'media',
            ])
            ->name('show');

        FlexRouter::post(
            '/{group}/update',
            [SettingsController::class, 'update']
        )
            ->whereIn('group', [
                'general',
                'mail',
                'media',
            ])
            ->name('update');
    });
