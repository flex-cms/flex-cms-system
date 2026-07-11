<?php

namespace Flex\Core\Controllers;

use Flex\Attributes\UseExceptions;
use Flex\Core\Filters\Shared\StatusFilter;
use Flex\Core\Traits\CrudHelper;
use Flex\Core\Traits\HandlesMedia;
use Flex\Core\Traits\HandlesTableFilters;
use Flex\Models\User;
use Flex\Models\Role;
use Flex\Core\Routing\View;
use Flex\Core\Controllers\BaseController;

class UserController extends BaseController
{
    use HandlesMedia, HandlesTableFilters, CrudHelper;

    protected string $indexTitle;
    protected string $createTitle;
    protected string $editTitle;
    protected string $createBtn;
    protected string $deleteSuccessMessage;
    protected string $deleteErrorMessage;

    public function __construct()
    {
        $this->indexTitle = 'Управление на потребители';
        $this->createTitle = 'Нов потребител';
        $this->editTitle = 'Редактиране на потребител';
        $this->createBtn = 'Нов потребител';
        $this->deleteSuccessMessage = 'Потребителят е изтрит успешно.';
        $this->deleteErrorMessage = 'Този потребител не съществува.';
    }

    #[UseExceptions]
    public function index()
    {
        $query = User::with('roles');

        if (!empty($_GET['role'])) {
            $roleParam = $_GET['role'];

            $query->whereHas('roles', function ($q) use ($roleParam) {
                if (is_numeric($roleParam)) {
                    $q->where('roles.id', $roleParam);
                } else {
                    $q->where('roles.slug', $roleParam);
                }
            });
        }

        $users = $this->applyFilters(
            $query->orderBy('created_at'),
            ['name', 'slug'],
            ['name', 'slug', 'created_at'],
            ['status' => StatusFilter::class],
            'fullname'
        )->get();

        $data = [
            'title' => $this->indexTitle,
            'users' => $users,
            'roles' => Role::orderBy('name', 'asc')->get(),
            'primaryButton' => $this->createButton('/admin/users/create', $this->createBtn)
        ];

        render_view('admin/users/index', $data);
    }

    #[UseExceptions]
    public function create()
    {
        $roles = Role::orderBy('name', 'asc')->get();

        $data = [
            'title' => $this->createTitle,
            'user' => new User(),
            'allRoles' => $roles,
            'assignedRoleIds' => [],
            'primaryButton' => $this->createButton('/admin/users/create', $this->createBtn)
        ];

        render_view('admin/users/form', $data);
    }

    #[UseExceptions]
    public function edit($id)
    {
        $user = User::with('roles')->findOrFail($id);
        $roles = Role::orderBy('name', 'asc')->get();
        $assignedRoleIds = $user->roles->pluck('id')->toArray();

        $data = [
            'title' => $this->editTitle,
            'user' => $user,
            'allRoles' => $roles,
            'assignedRoleIds' => $assignedRoleIds,
            'primaryButton' => $this->createButton('/admin/users/create', $this->createBtn)
        ];

        render_view('admin/users/form', $data);
    }

    #[UseExceptions]
    public function store()
    {
        $data = $this->prepareData($_POST);
        $user = User::create($data);

        if (isset($_POST['roles']) && is_array($_POST['roles'])) {
            $user->roles()->sync(array_keys($_POST['roles']));
        }

        View::redirect('/admin/users/edit/' . $user->id);
    }

    #[UseExceptions]
    public function update($id)
    {
        $user = User::findOrFail($id);

        $data = $this->prepareData($_POST, $user);
        $user->update($data);

        $this->syncUserRoles($user, $_POST);

        $user->refresh();

        View::redirect('/admin/users/edit/' . $user->id);
    }

    #[UseExceptions]
    public function delete()
    {
        return $this->deleteRecord(User::class);
    }

    #[UseExceptions]
    public function toggle()
    {
        $result = $this->toggleStatus(User::class, 'is_active');

        if (!$result['success']) {
            return $this->jsonResponse(false, $result['message']);
        }

        $statusText = $result['new_status'] ? 'активиран' : 'деактивиран';

        return $this->jsonResponse(true, "Потребителят беше {$statusText} успешно!");
    }

    private function prepareData(array $post, $model = null): array
    {
        $data = $this->buildUpdateData($post, $model, [
            'fullname',
            'email',
            'is_active',
            'created_at' => 'default_date'
        ]);

        if (!empty($post['password'])) {
            $data['password'] = $post['password'];
        }

        $currentOptions = !empty($model->options) ? $model->options->getArrayCopy() : [];
        $data['options'] = $this->mergeOptions($post, $currentOptions);
        $data['options'] = $this->handleFileUploads($data['options'], 'users');

        return $data;
    }

    private function syncUserRoles($user, array $post): void
    {
        if (isset($post['roles']) && is_array($post['roles'])) {
            $activeRoles = array_keys(array_filter($post['roles'], function ($val) {
                return (string) $val === '1' || $val === 'on' || $val === true;
            }));
            $user->roles()->sync($activeRoles);
        } else {
            $user->roles()->sync([]);
        }
    }
}