<?php

declare(strict_types=1);

use Flex\Features\Shopping\Providers\ShoppingServiceProvider;

return [
    'enabled' => true,
    'priority' => 20,

    'providers' => [
        ShoppingServiceProvider::class,
    ],

    'routes' => [
        'admin' => 'Routes/admin.php',
        'api' => 'Routes/api.php',
    ],
];
