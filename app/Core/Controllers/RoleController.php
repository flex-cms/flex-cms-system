<?php

namespace Flex\Core\Controllers;

use Exception;
use Flex\Attributes\UseExceptions;
use Flex\Core\Filters\Shared\StatusFilter;
use Flex\Core\Helpers\Flash;
use Flex\Core\Helpers\Str;
use Flex\Core\Routing\View;
use Flex\Core\Traits\CrudHelper;
use Flex\Core\Traits\HandlesTableFilters;
use Flex\Core\Traits\RequestHelper;
use Flex\Models\Permission;
use Flex\Models\Role;

class RoleController extends BaseController
{
    use CrudHelper, HandlesTableFilters, RequestHelper;

    protected string $indexTitle = 'Управление на роли';
    protected string $createTitle = 'Нова роля';
    protected string $editTitle = 'Редактиране на роля';
    protected array $messages = [];

    public function __construct()
    {
        $this->initMessages();
    }

    private function initMessages(): void
    {
        $this->messages = [
            'delete_success' => 'Ролята беше успешно преместена в кошчето.',
            'restore_success' => 'Ролята е възстановена успешно.',
            'force_delete_success' => 'Ролята е изтрита перманентно.',
            'toggle_active' => 'Ролята беше активирана успешно!',
            'toggle_inactive' => 'Ролята беше деактивирана успешно!',
            'not_found' => 'Тази роля не съществува.',
            'trashed_edit' => 'Не може да редактирате изтрита роля.'
        ];
    }

    #[UseExceptions]
    public function index()
    {
        $roles = $this->applyFilters(
            Role::query()->withTrashed(),
            ['name', 'slug'],
            ['name', 'slug', 'created_at'],
            ['status' => StatusFilter::class],
            'name'
        )->get();

        $data = [
            'title' => $this->indexTitle,
            'roles' => $roles,
            'primaryButton' => $this->createButton('/admin/users/roles/create', 'Нова роля')
        ];

        render_view('admin/roles/index', $data);
    }

    #[UseExceptions]
    public function create()
    {
        $data = [
            'title' => $this->createTitle,
            'roles' => Role::all()->groupBy('module'),
            'role' => new Role(),
            'primaryButton' => $this->createButton('/admin/users/roles/create', 'Нова роля')
        ];

        render_view('admin/roles/form', $data);
    }

    #[UseExceptions]
    public function store()
    {
        $data = $this->prepareData($_POST);
        $role = Role::create($data);

        $this->handleDefaultRole($role, $data);
        $role->permissions()->sync($_POST['permissions'] ?? []);

        Flash::success('Ролята беше създадена успешно!');
        View::redirect('/admin/users/roles');
    }

    #[UseExceptions]
    public function edit($id)
    {
        $role = Role::findOrFail($id);

        $data = [
            'title' => $this->editTitle,
            'role' => $role,
            'permissions' => Permission::all()->groupBy('module'),
            'assignedPermissions' => $role->permissions->pluck('id')->toArray(),
            'primaryButton' => $this->createButton('/admin/users/roles/create', 'Нова роля')
        ];

        render_view('admin/roles/form', $data);
    }

    #[UseExceptions]
    public function update($id)
    {
        $role = Role::withTrashed()->find($id);
        if (!$role)
            throw new Exception($this->messages['not_found']);
        if ($role->trashed())
            throw new Exception($this->messages['trashed_edit']);

        $data = $this->prepareData($_POST, $role);

        $this->handleDefaultRole($role, $data);
        $role->update($data);

        $permissions = is_array($_POST['permissions'] ?? null) ? array_keys($_POST['permissions']) : ($_POST['permissions'] ?? []);
        $role->permissions()->sync($permissions);

        Flash::success('Ролята беше обновена успешно!');
        View::redirect('/admin/users/roles/edit/' . $id);
    }

    #[UseExceptions]
    public function delete()
    {
        $this->deleteRecord(Role::class);
        return $this->jsonResponse(true, $this->messages['delete_success']);
    }

    #[UseExceptions]
    public function restore()
    {
        $this->restoreRecord(Role::class);
        return $this->jsonResponse(true, $this->messages['restore_success']);
    }

    #[UseExceptions]
    public function forceDelete()
    {
        $this->forceDeleteRecord(Role::class);
        return $this->jsonResponse(true, $this->messages['force_delete_success']);
    }

    #[UseExceptions]
    public function toggle()
    {
        $result = $this->toggleStatus(Role::class, 'is_active');
        $msgKey = $result['new_status'] ? 'toggle_active' : 'toggle_inactive';
        return $this->jsonResponse($result['success'], $this->messages[$msgKey]);
    }

    private function prepareData(array $post, $model = null): array
    {
        $post = $this->normalizeCheckboxes($post);

        // Ръчна обработка на специфичните полета
        $post['slug'] = !empty($post['slug']) ? Str::slug($post['slug']) : Str::slug($post['name'] ?? '');
        $post['priority'] = (int) ($post['priority'] ?? 0);
        $post['options'] = ['schedule' => $this->parseSchedule($post)];

        return $this->buildUpdateData($post, $model, [
            'name',
            'slug',
            'description',
            'priority',
            'is_active',
            'is_default',
            'color',
            'options'
        ]);
    }

    private function handleDefaultRole($role, array $data): void
    {
        if (!empty($data['is_default'])) {
            Role::where('id', '!=', $role->id)->update(['is_default' => false]);
        }
    }

    private function parseSchedule(array $post): array
    {
        $rawSchedule = $post['schedule'] ?? [];
        $filteredSchedule = [];

        if (isset($post['has_time_limit'])) {
            foreach ($rawSchedule as $dayNum => $data) {
                if (isset($data['active'])) {
                    $filteredSchedule[$dayNum] = [
                        'start' => $data['start'] ?? '09:00',
                        'end' => $data['end'] ?? '18:00'
                    ];
                }
            }
        }
        return $filteredSchedule;
    }
}
