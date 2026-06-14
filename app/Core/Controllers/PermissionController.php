<?php

namespace Flex\Core\Controllers;

use Flex\Attributes\UseExceptions;
use Flex\Core\Routing\View;
use Flex\Models\Permission;
use Flex\Models\Role;

class PermissionController extends BaseController
{
    #[UseExceptions]
    public function index()
    {
        $query = Permission::orderBy('module', 'asc')->orderBy('name', 'asc');

        if (!empty($_GET['module'])) {
            $query->where('module', '=', $_GET['module']);
        }

        if (!empty($_GET['search'])) {
            $search = trim($_GET['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        $permissions = $query->get();

        $this->render(View::make('admin/permissions/index', [
            'title' => 'Разрешения',
            'permissions' => $permissions,
            'primaryButton' => [
                'label' => 'Ново разрешение',
                'url' => '/admin/users/permissions/create',
                'icon' => 'fa-plus'
            ]
        ], 'admin'));
    }

    #[UseExceptions]
    public function create()
    {
        $allRoles = Role::orderBy('name', 'asc')->get();
        $this->render(View::make('admin/permissions/form', [
            'title' => 'Създаване на ново разрешение',
            'allRoles' => $allRoles,
            'assignedRoleIds' => []
        ], 'admin'));
    }

    #[UseExceptions]
    public function store()
    {
        $data = $this->getPermissionData();
        $permission = Permission::create($data);

        $permission->roles()->sync(array_keys($_POST['roles'] ?? []));

        View::redirect('/admin/users/permissions');
    }

    #[UseExceptions]
    public function edit($id)
    {
        $permission = Permission::findOrFail($id);
        $allRoles = Role::orderBy('name', 'asc')->get();
        $assignedRoleIds = $permission->roles()->pluck('roles.id')->toArray();

        $this->render(View::make('admin/permissions/form', [
            'title'=> 'Редактиране на разрешение',
            'permission' => $permission,
            'allRoles' => $allRoles,
            'assignedRoleIds' => $assignedRoleIds
        ], 'admin'));
    }

    #[UseExceptions]
    public function update($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->update($this->getPermissionData());

        $permission->roles()->sync(array_keys($_POST['roles'] ?? []));

        View::redirect('/admin/users/permissions');
    }

    private function getPermissionData(): array
    {
        return [
            'name' => $_POST['name'] ?? '',
            'slug' => $_POST['slug'] ?? '',
            'module' => $_POST['module'] ?? 'General',
            'description' => $_POST['description'] ?? ''
        ];
    }
}
