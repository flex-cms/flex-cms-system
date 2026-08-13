<?php

declare(strict_types=1);

use Flex\Features\Pages\Providers\PagesServiceProvider;

return [
    'enabled' => true,
    'priority' => 8,
    'providers' => [
        PagesServiceProvider::class,
    ],
    'routes' => [
        'admin' => 'Routes/admin.php',
        'api' => 'Routes/api.php',
    ],
];
