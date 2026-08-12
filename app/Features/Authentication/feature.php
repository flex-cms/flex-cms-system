<?php

declare(strict_types=1);

use Flex\Features\Authentication\Providers\AuthenticationServiceProvider;

return [
    'enabled' => true,
    'priority' => 5,

    'providers' => [
        AuthenticationServiceProvider::class,
    ],

    'routes' => [
        'web' => 'Routes/web.php',
        'admin' => 'Routes/admin.php',
        'api' => 'Routes/api.php',
    ],
];
