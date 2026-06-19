<?php

namespace Flex\Core\Controllers;

use Flex\Attributes\UseExceptions;
use Flex\Core\Filters\Shared\StatusFilter;
use Flex\Core\Routing\View;
use Flex\Core\Traits\CrudHelper;
use Flex\Core\Traits\HandlesTableFilters;
use Flex\Models\Permission;
use Flex\Models\Role;

class PermissionController extends BaseController
{
    use CrudHelper, HandlesTableFilters;
    
    protected string $indexTitle;
    protected string $createTitle;
    protected string $editTitle;
    protected string $createBtn;
    protected string $deleteSuccessMessage;
    protected string $deleteErrorMessage;

    public function __construct()
    {
        $this->indexTitle = 'Управление на разрешения';
        $this->createTitle = 'Създаване на ново разрешение';
        $this->editTitle = 'Редактиране на разрешението';
        $this->createBtn = 'Ново разрешение';
        $this->deleteSuccessMessage = 'Изтрито успешно.';
        $this->deleteErrorMessage = 'Това разрешение не съществува.';
    }

    #[UseExceptions]
    public function index()
    {
        $permissions = $this->applyFilters(
            Permission::query(),
            ['name', 'slug'],
            ['name', 'slug', 'created_at'],
            ['status' => StatusFilter::class],
            'name'
        )->get();

        $data = [
            'title' => $this->indexTitle,
            'permissions' => $permissions,
            'primaryButton' => $this->createButton('/admin/users/permissions/create', $this->createTitle)
        ];

        render_view('admin/permissions/index', $data);
    }

    #[UseExceptions]
    public function create()
    {
        $allRoles = Role::orderBy('name', 'asc')->get();
        $data = [
            'title' => 'Създаване на ново разрешение',
            'allRoles' => $allRoles,
            'assignedRoleIds' => []
        ];

        render_view('admin/permissions/form', $data);
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

        $data = [
            'title'=> 'Редактиране на разрешение',
            'permission' => $permission,
            'allRoles' => $allRoles,
            'assignedRoleIds' => $assignedRoleIds
        ];
        
        render_view('admin/permissions/form', $data);
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
