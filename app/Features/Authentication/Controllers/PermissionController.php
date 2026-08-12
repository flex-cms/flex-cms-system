<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Controllers;

use Flex\Core\View\ViewResponse;
use Flex\Features\AdminUI\Services\AdminUIRenderer;
use Flex\Features\Authentication\Models\Permission;

final readonly class PermissionController
{
    public function __construct(private AdminUIRenderer $adminUI) {}
    public function index(): ViewResponse
    {
        return $this->adminUI->response('Authentication::permissions/index', [
            'title' => 'Разрешения',
            'permissions' => Permission::query()->withCount('roles')->orderBy('module')->orderBy('slug')->get(),
        ]);
    }
}
