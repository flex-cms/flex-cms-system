<?php

namespace Flex\Core\Controllers;

use Flex\Core\Routing\View;
use Flex\Models\Permission;

class PermissionController extends BaseController
{
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

    public function create()
    {
        $this->render(View::make('admin/permissions/form', [
            'title' => 'Ново разрешение'
        ], 'admin'));
    }

    public function store()
    {
        $permission = Permission::create($this->getPermissionDataFromRequest());
        View::redirect('/admin/users/permissions/edit/' . $permission->id);
    }

    public function edit($id)
    {
        $permission = Permission::findOrFail($id);

        $this->render(View::make('admin/permissions/form', [
            'title' => 'Редактиране: ' . $permission->name,
            'permission' => $permission
        ], 'admin'));
    }

    public function update($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->update($this->getPermissionDataFromRequest());

        View::redirect('/admin/users/permissions/edit/' . $id);
    }

    public function delete($id)
    {
        Permission::findOrFail($id)->delete();
        View::redirect('/admin/users/permissions');
    }

    private function getPermissionDataFromRequest(): array
    {
        return [
            'name' => $_POST['name'] ?? '',
            'slug' => $_POST['slug'] ?? '',
            'module' => $_POST['module'] ?? 'General',
            'description' => $_POST['description'] ?? ''
        ];
    }
}
