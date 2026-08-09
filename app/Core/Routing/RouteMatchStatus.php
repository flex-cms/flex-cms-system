<?php

declare(strict_types=1);

namespace Flex\Core\Routing;

enum RouteMatchStatus: string
{
    case Found = 'found';
    case MethodNotAllowed = 'method_not_allowed';
    case NotFound = 'not_found';
}
