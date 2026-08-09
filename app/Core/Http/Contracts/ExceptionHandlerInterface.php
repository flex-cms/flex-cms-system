<?php

declare(strict_types=1);

namespace Flex\Core\Http\Contracts;

use Flex\Core\Http\Request;
use Flex\Core\Http\Response;
use Throwable;

interface ExceptionHandlerInterface
{
    public function render(Request $request, Throwable $exception): Response;
}
