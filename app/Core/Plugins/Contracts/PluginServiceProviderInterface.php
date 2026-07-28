<?php

namespace Flex\Core\Plugins\Contracts;

interface PluginServiceProviderInterface
{
    public function register(): void;

    public function boot(): void;
}