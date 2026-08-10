<?php

declare(strict_types=1);

namespace Flex\Features\Dashboard\Controllers;

use Flex\Core\View\ViewResponse;
use Flex\Features\AdminUI\Services\AdminUIRenderer;
use Flex\Features\Dashboard\Services\DashboardService;

final readonly class DashboardController
{
    public function __construct(
        private DashboardService $dashboard,
        private AdminUIRenderer $adminUI,
    ) {}

    public function index(): ViewResponse
    {
        return $this->adminUI->response(
            'Dashboard::index',
            $this->dashboard->data()->toArray(),
        );
    }
}
