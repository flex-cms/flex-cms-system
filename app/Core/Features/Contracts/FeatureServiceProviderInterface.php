<?php

declare(strict_types=1);

namespace Flex\Core\Features\Contracts;

use Flex\Core\Container\Container;

interface FeatureServiceProviderInterface
{
    /**
     * Регистрира container bindings на Feature-а.
     *
     * Този метод не трябва да изпълнява маршрути,
     * database заявки или друга runtime логика.
     */
    public function register(Container $container): void;
}
