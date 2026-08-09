<?php

declare(strict_types=1);

namespace Flex\Core\View\Contracts;

use Flex\Core\View\ViewResponse;

interface ViewRendererInterface
{
    public function render(string $view, array $data = [], ?string $layout = null): string;
    public function response(string $view, array $data = [], ?string $layout = null, int $status = 200): ViewResponse;
}
