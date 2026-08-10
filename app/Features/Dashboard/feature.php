<?php

declare(strict_types=1);

use Flex\Features\Dashboard\Providers\DashboardServiceProvider;

return [
    'enabled' => true,
    'priority' => 3,
    'providers' => [DashboardServiceProvider::class],
    'routes' => ['admin' => 'Routes/admin.php'],
];
