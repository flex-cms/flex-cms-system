<?php

declare(strict_types=1);

use Flex\Core\Routing\FlexRouter;
use Flex\Features\SystemHealth\Controllers\HealthController;

FlexRouter::get('/api/flex/health', [HealthController::class, 'index'])
    ->name('api.flex.health');
