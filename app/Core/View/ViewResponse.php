<?php

declare(strict_types=1);

namespace Flex\Core\View;

use Flex\Core\Http\Response;

final class ViewResponse extends Response
{
    public function __construct(string $html, int $status = 200, array $headers = [])
    {
        parent::__construct($html, $status, [
            'Content-Type' => 'text/html; charset=UTF-8',
            ...$headers,
        ]);
    }
}
