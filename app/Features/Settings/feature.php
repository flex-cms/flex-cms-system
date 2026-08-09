<?php

declare(strict_types=1);
use Flex\Features\Settings\Providers\SettingsServiceProvider;

return [
    'enabled' => true,
    'priority' => 10,

    'providers' => [
        SettingsServiceProvider::class,
    ],

    'routes' => [
        'admin' => 'Routes/admin.php',
    ],
];