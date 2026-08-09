<?php

declare(strict_types=1);

namespace Flex\Core\Http\Exceptions;

final class NotFoundHttpException extends HttpException
{
    public function __construct(string $message = 'Not Found')
    {
        parent::__construct(404, $message);
    }
}
