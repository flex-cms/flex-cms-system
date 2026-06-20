<?php

namespace Flex\Core\Controllers;

use Exception;
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
    protected string $restoreSuccessMessage;
    protected string $restoreErrorMessage;
    protected string $forceDeleteSuccessMessage;
    protected string $forceDeleteErrorMessage;
    protected string $trashedEditErrorMessage;

    public function __construct()
    {
        $this->indexTitle = 'Управление на разрешения';
        $this->createTitle = 'Създаване на ново разрешение';
        $this->editTitle = 'Редактиране на разрешението';
        $this->createBtn = 'Ново разрешение';
        $this->deleteSuccessMessage = 'Изтрито успешно.';
        $this->deleteErrorMessage = 'Това разрешение не съществува.';
        $this->restoreSuccessMessage = 'Разрешението е възстановено успешно.';
        $this->restoreErrorMessage = 'Грешка при възстановяване на разрешението.';
        $this->forceDeleteSuccessMessage = 'Разрешението е изтрито перманентно.';
        $this->forceDeleteErrorMessage = 'Грешка при перманентното изтриване.';
        $this->trashedEditErrorMessage = 'Не може да редактирате изтрито разрешение.';
    }

    #[UseExceptions]
    public function index()
    {
        $query = Permission::query();

        if (!empty($_GET['module'])) {
            $query->where('module', 'LIKE', $_GET['module']);
        }

        $permissions = $this->applyFilters(
            $query,
            ['name', 'slug'],
            ['name', 'slug', 'created_at'],
            [
                'status' => StatusFilter::class,
            ],
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
            'title' => $this->createTitle,
            'allRoles' => $allRoles,
            'assignedRoleIds' => [],
            'primaryButton' => $this->createButton('/admin/users/permissions/create', $this->createTitle)
        ];

        render_view('admin/permissions/form', $data);
    }

    #[UseExceptions]
    public function store()
    {
        $data = $this->getPermissionData();
        $permission = Permission::create($data);

        $permission->roles()->sync(array_keys($_POST['roles'] ?? []));

        View::redirect('/admin/users/permissions/edit/' . $permission->id);
    }

    #[UseExceptions]
    public function edit($id)
    {
        $permission = Permission::withTrashed()->find($id);

        if (!$permission) {
            throw new Exception($this->deleteErrorMessage);
        }

        if ($permission->trashed()) {
            throw new Exception($this->trashedEditErrorMessage);
        }

        $allRoles = Role::orderBy('name', 'asc')->get();
        $assignedRoleIds = $permission->roles()->pluck('roles.id')->toArray();

        $data = [
            'title' => $this->editTitle,
            'permission' => $permission,
            'allRoles' => $allRoles,
            'assignedRoleIds' => $assignedRoleIds,
            'primaryButton' => $this->createButton('/admin/users/permissions/create', $this->createTitle)
        ];

        render_view('admin/permissions/form', $data);
    }

    #[UseExceptions]
    public function update($id)
    {
        $permission = Permission::withTrashed()->find($id);

        if (!$permission) {
            throw new Exception($this->deleteErrorMessage);
        }

        if ($permission->trashed()) {
            throw new Exception($this->trashedEditErrorMessage);
        }

        $permission->update($this->getPermissionData());
        $permission->roles()->sync(array_keys($_POST['roles'] ?? []));

        View::redirect('/admin/users/permissions/edit/' . $id);
    }

    #[UseExceptions]
    public function delete()
    {
        return $this->deleteRecord(Permission::class);
    }

    #[UseExceptions]
    public function restore()
    {
        return $this->restoreRecord(Permission::class);
    }

    #[UseExceptions]
    public function forceDelete()
    {
        return $this->forceDeleteRecord(Permission::class);
    }

    #[UseExceptions]
    public function toggle()
    {
        $result = $this->toggleStatus(Permission::class, 'is_active');

        if (!$result['success']) {
            return $this->jsonResponse(false, $result['message']);
        }

        $statusText = $result['new_status'] ? 'активирано' : 'деактивирано';
        
        return $this->jsonResponse(true, "Разрешението беше {$statusText} успешно!");
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
