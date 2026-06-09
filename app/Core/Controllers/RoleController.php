<?php

namespace Flex\Core\Controllers;

use Flex\Core\Helpers\Str;
use Flex\Core\Routing\View;
use Flex\Core\Traits\CrudHelper;
use Flex\Models\Role;
use Flex\Models\Permission;

class RoleController extends BaseController
{
    use CrudHelper;
    
    public function index()
    {
        $query = Role::orderBy('name', 'asc');

        if (!empty($_GET['status'])) {
            $isActive = ($_GET['status'] === 'active') ? 1 : 0;
            $query->where('is_active', $isActive);
        }

        if (!empty($_GET['search'])) {
            $search = trim($_GET['search']);
            $query->where('name', 'LIKE', "%{$search}%");
        }

        $roles = $query->get();

        $this->render(View::make('admin/roles/index', [
            'title' => 'Роли и права',
            'roles' => $roles,
            'primaryButton' => [
                'label' => 'Нова роля',
                'url' => '/admin/users/roles/create',
                'icon' => 'fa-plus'
            ]
        ], 'admin'));
    }

    public function create()
    {
        $permissions = Permission::all()->groupBy('module');

        $this->render(View::make('admin/roles/form', [
            'title' => 'Нова роля',
            'permissions' => $permissions
        ], 'admin'));
    }

    public function store()
    {
        $data = $this->getRoleDataFromRequest();
        $role = Role::create($data);

        if ($data['is_default'] === true) {
            Role::where('id', '>', 0)->update(['is_default' => false]);
        }

        if ($role) {
            $role->permissions()->sync($_POST['permissions'] ?? []);
        }

        View::redirect('/admin/users/roles');
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::all()->groupBy('module');
        $assignedPermissions = $role->permissions->pluck('id')->toArray();

        $this->render(View::make('admin/roles/form', [
            'title' => 'Редактиране на роля: ' . $role->name,
            'role' => $role,
            'permissions' => $permissions,
            'assignedPermissions' => $assignedPermissions
        ], 'admin'));
    }

    public function update($id)
    {
        $role = Role::findOrFail($id);
        $data = $this->getRoleDataFromRequest();

        if ($data['is_default'] === true) {
            Role::where('id', '!=', $id)->update(['is_default' => false]);
        }

        if (isset($_POST['permissions']) && is_array($_POST['permissions'])) {
            $_POST['permissions'] = array_keys($_POST['permissions']);
        }

        $role->update($data);
        $role->permissions()->sync($_POST['permissions'] ?? []);

        View::redirect('/admin/users/roles/edit/' . $id);
    }

    public function toggle()
    {
        $result = $this->toggleStatus(Role::class, 'is_active');

        if (!$result['success']) {
            return $this->jsonResponse(false, $result['message']);
        }

        $statusText = $result['new_status'] ? 'активирана' : 'деактивирана';
        
        return $this->jsonResponse(true, "Ролята беше {$statusText} успешно!");
    }

    private function getRoleDataFromRequest(): array
    {
        $rawSchedule = $_POST['schedule'] ?? [];
        $filteredSchedule = [];

        if (isset($_POST['has_time_limit'])) {
            foreach ($rawSchedule as $dayNum => $data) {
                if (isset($data['active'])) {
                    $filteredSchedule[$dayNum] = [
                        'start' => $data['start'] ?? '09:00',
                        'end' => $data['end'] ?? '18:00'
                    ];
                }
            }
        }

        $slug = !empty($_POST['slug'])
            ? Str::slug($_POST['slug'])
            : Str::slug($_POST['name'] ?? '');

        return [
            'name' => $_POST['name'] ?? '',
            'slug' => $slug,
            'description' => $_POST['description'] ?? '',
            'priority' => (int) ($_POST['priority'] ?? 0),
            'color' => $_POST['color'] ?? '#6366f1',
            'is_active' => isset($_POST['is_active']),
            'is_default' => isset($_POST['is_default']),
            'options' => [
                'schedule' => $filteredSchedule
            ]
        ];
    }
}