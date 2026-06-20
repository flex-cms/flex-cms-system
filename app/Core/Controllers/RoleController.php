<?php

namespace Flex\Core\Controllers;

use Exception;
use Flex\Attributes\UseExceptions;
use Flex\Core\Filters\Shared\StatusFilter;
use Flex\Core\Helpers\Str;
use Flex\Core\Routing\View;
use Flex\Core\Traits\CrudHelper;
use Flex\Core\Traits\HandlesTableFilters;
use Flex\Models\Role;
use Flex\Models\Permission;

class RoleController extends BaseController
{
    use CrudHelper, HandlesTableFilters;

    protected string $indexTitle;
    protected string $createTitle;
    protected string $editTitle;
    protected string $createBtn;
    protected string $deleteSuccessMessage;
    protected string $deleteErrorMessage;
    protected string $trashedEditErrorMessage;
    protected string $restoreSuccessMessage;
    protected string $restoreErrorMessage;
    protected string $forceDeleteSuccessMessage;
    protected string $forceDeleteErrorMessage;

    public function __construct()
    {
        $this->indexTitle = 'Управление на роли';
        $this->createTitle = 'Нова роля';
        $this->editTitle = 'Редактиране на роля';
        $this->createBtn = 'Нова роля';
        $this->deleteSuccessMessage = 'Изтрито успешно.';
        $this->deleteErrorMessage = 'Тази роля не съществува.';
        $this->trashedEditErrorMessage = 'Не може да редактирате изтрита роля.';
        $this->restoreSuccessMessage = 'Ролята е възстановена успешно.';
        $this->restoreErrorMessage = 'Грешка при възстановяване на ролята.';
        $this->forceDeleteSuccessMessage = 'Ролята е изтрита перманентно.';
        $this->forceDeleteErrorMessage = 'Грешка при перманентното изтриване.';
    }

    #[UseExceptions]
    public function index()
    {
        $query = Role::query()->withTrashed();

        $roles = $this->applyFilters(
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
            'roles' => $roles,
            'primaryButton' => $this->createButton('/admin/users/roles/create', $this->createTitle)
        ];

        render_view('admin/roles/index', $data);
    }

    #[UseExceptions]
    public function create()
    {
        $roles = Role::all()->groupBy('module');

        $data = [
            'title' => $this->createTitle,
            'roles' => $roles,
            'primaryButton' => $this->createButton('/admin/users/roles/create', $this->createBtn)
        ];

        render_view('admin/roles/form', $data);
    }

    #[UseExceptions]
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

    #[UseExceptions]
    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $permissions = Permission::all()->groupBy('module');
        $assignedPermissions = $role->permissions->pluck('id')->toArray();

        $data = [
            'title' => $this->editTitle,
            'role' => $role,
            'permissions' => $permissions,
            'assignedPermissions' => $assignedPermissions,
            'primaryButton' => $this->createButton('/admin/roles/create', $this->createBtn)
        ];

        render_view('admin/roles/form', $data);
    }

    #[UseExceptions]
    public function update($id)
    {
        $role = Role::withTrashed()->find($id);

        if (!$role) {
            throw new Exception($this->deleteErrorMessage);
        }

        if ($role->trashed()) {
            throw new Exception($this->trashedEditErrorMessage);
        }

        $data = $this->getRoleDataFromRequest();

        if ($data['is_default'] === true) {
            Role::where('id', '!=', $id)->update(['is_default' => false]);
        }

        $permissions = $_POST['permissions'] ?? [];
        if (is_array($permissions)) {
            $permissions = array_keys($permissions);
        }

        $role->update($data);
        $role->permissions()->sync($permissions);

        View::redirect('/admin/users/roles/edit/' . $id);
    }

    #[UseExceptions]
    public function toggle()
    {
        $result = $this->toggleStatus(Role::class, 'is_active');

        if (!$result['success']) {
            return $this->jsonResponse(false, $result['message']);
        }

        $statusText = $result['new_status'] ? 'активирана' : 'деактивирана';

        return $this->jsonResponse(true, "Ролята беше {$statusText} успешно!");
    }

    #[UseExceptions]
    public function delete()
    {
        return $this->deleteRecord(Role::class);
    }

    #[UseExceptions]
    public function restore()
    {
        return $this->restoreRecord(Role::class);
    }

    #[UseExceptions]
    public function forceDelete()
    {
        return $this->forceDeleteRecord(Role::class);
    }

    #[UseExceptions]
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
            'is_active' => !empty($_POST['is_active']), 
            'is_default' => !empty($_POST['is_default']),
            'color' => $_POST['color'] ?? '#6366f1',
            'options' => [
                'schedule' => $filteredSchedule
            ]
        ];
    }
}
