<?php

namespace Flex\Core\Controllers;

use Flex\Core\Routing\View;
use Flex\Models\Permission;

class PermissionController extends BaseController
{
    public function index()
    {
        $permissions = Permission::orderBy('name', 'asc')->get();

        $this->render(View::make('admin/permissions/index', [
            'title' => 'Разрешения',
            'permissions' => $permissions,
            'primaryButton' => [
                'label' => 'Ново разрешение',
                'url' => '/admin/permissions/create',
                'icon' => 'fa-plus'
            ]
        ], 'admin'));
    }
}