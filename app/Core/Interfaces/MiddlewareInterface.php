<?php

namespace Flex\Core\Interfaces;

interface MiddlewareInterface
{
    public function handle(): void;
}
