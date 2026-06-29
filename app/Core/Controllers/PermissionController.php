<?php

namespace Flex\Core\Controllers;

use Exception;
use Flex\Attributes\UseExceptions;
use Flex\Core\Filters\Shared\StatusFilter;
use Flex\Core\Helpers\Flash;
use Flex\Core\Routing\View;
use Flex\Core\Traits\CrudHelper;
use Flex\Core\Traits\HandlesTableFilters;
use Flex\Core\Traits\RequestHelper;
use Flex\Models\Permission;
use Flex\Models\Role;

class PermissionController extends BaseController
{
    use CrudHelper, HandlesTableFilters, RequestHelper;

    protected string $indexTitle = 'Управление на разрешения';
    protected string $createTitle = 'Създаване на ново разрешение';
    protected string $editTitle = 'Редактиране на разрешението';
    protected array $messages = [];

    public function __construct()
    {
        $this->initMessages();
    }

    private function initMessages(): void
    {
        $this->messages = [
            'delete_success' => 'Разрешението беше успешно преместено в кошчето.',
            'restore_success' => 'Разрешението е възстановено успешно.',
            'force_delete_success' => 'Разрешението е изтрито перманентно.',
            'toggle_active' => 'Разрешението беше активирано успешно!',
            'toggle_inactive' => 'Разрешението беше деактивирано успешно!',
            'not_found' => 'Това разрешение не съществува.',
            'trashed_edit' => 'Не може да редактирате изтрито разрешение.',
            'create_success' => 'Разрешението беше създадено успешно!',
            'update_success' => 'Разрешението беше обновено успешно!'
        ];
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
            ['status' => StatusFilter::class],
            'name'
        )->get();

        $data = [
            'title' => $this->indexTitle,
            'permissions' => $permissions,
            'primaryButton' => $this->createButton('/admin/users/permissions/create', 'Ново разрешение')
        ];

        render_view('admin/permissions/index', $data);
    }

    #[UseExceptions]
    public function create()
    {
        $data = [
            'title' => $this->createTitle,
            'allRoles' => Role::orderBy('name', 'asc')->get(),
            'assignedRoleIds' => [],
            'permission' => new Permission(),
            'primaryButton' => $this->createButton('/admin/users/permissions/create', 'Ново разрешение')
        ];

        render_view('admin/permissions/form', $data);
    }

    #[UseExceptions]
    public function store()
    {
        $data = $this->prepareData($_POST);
        $permission = Permission::create($data);

        $permission->roles()->sync(array_keys($_POST['roles'] ?? []));

        Flash::success($this->messages['create_success']);
        View::redirect('/admin/users/permissions/edit/' . $permission->id);
    }

    #[UseExceptions]
    public function edit($id)
    {
        $permission = Permission::withTrashed()->find($id);

        if (!$permission)
            throw new Exception($this->messages['not_found']);
        if ($permission->trashed())
            throw new Exception($this->messages['trashed_edit']);

        $data = [
            'title' => $this->editTitle,
            'permission' => $permission,
            'allRoles' => Role::orderBy('name', 'asc')->get(),
            'assignedRoleIds' => $permission->roles()->pluck('roles.id')->toArray(),
            'primaryButton' => $this->createButton('/admin/users/permissions/create', 'Ново разрешение')
        ];

        render_view('admin/permissions/form', $data);
    }

    #[UseExceptions]
    public function update($id)
    {
        $permission = Permission::withTrashed()->find($id);

        if (!$permission)
            throw new Exception($this->messages['not_found']);
        if ($permission->trashed())
            throw new Exception($this->messages['trashed_edit']);

        $permission->update($this->prepareData($_POST, $permission));
        $permission->roles()->sync(array_keys($_POST['roles'] ?? []));

        Flash::success($this->messages['update_success']);
        View::redirect('/admin/users/permissions/edit/' . $id);
    }

    #[UseExceptions]
    public function delete()
    {
        $this->deleteRecord(Permission::class);
        return $this->jsonResponse(true, $this->messages['delete_success']);
    }

    #[UseExceptions]
    public function restore()
    {
        $this->restoreRecord(Permission::class);
        return $this->jsonResponse(true, $this->messages['restore_success']);
    }

    #[UseExceptions]
    public function forceDelete()
    {
        $this->forceDeleteRecord(Permission::class);
        return $this->jsonResponse(true, $this->messages['force_delete_success']);
    }

    #[UseExceptions]
    public function toggle()
    {
        $result = $this->toggleStatus(Permission::class, 'is_active');
        $msgKey = $result['new_status'] ? 'toggle_active' : 'toggle_inactive';
        return $this->jsonResponse($result['success'], $this->messages[$msgKey]);
    }

    private function prepareData(array $post, $model = null): array
    {
        $post = $this->normalizeCheckboxes($post);

        return $this->buildUpdateData($post, $model, [
            'name',
            'slug',
            'module',
            'description',
            'is_active'
        ]);
    }
}
