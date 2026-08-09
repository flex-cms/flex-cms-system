<?php

declare(strict_types=1);

namespace Flex\Core\Http\Contracts;

use Flex\Core\Http\Request;
use Flex\Core\Http\Response;

interface RequestHandlerInterface
{
    public function handle(Request $request): Response;
}
