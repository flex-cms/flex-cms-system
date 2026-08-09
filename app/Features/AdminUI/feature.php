<?php

declare(strict_types=1);

use Flex\Features\AdminUI\Providers\AdminUIServiceProvider;

return [
    'enabled' => true,
    'priority' => 2,

    'providers' => [
        AdminUIServiceProvider::class,
    ],

    'routes' => [
        'admin' => 'Routes/admin.php',
    ],
];
