<?php

namespace Flex\Core\Controllers;

use Flex\Attributes\UseExceptions;
use Flex\Core\Traits\CrudHelper;
use Flex\Core\Traits\HandlesTableFilters;
use Flex\Core\Traits\RequestHelper;
use Flex\Models\User;
use Flex\Models\Role;
use Flex\Core\Routing\View;
use Flex\Core\Controllers\BaseController;

class UserController extends BaseController
{
    use HandlesTableFilters, RequestHelper, CrudHelper;

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

        if (!empty($_GET['search'])) {
            $search = $_GET['search'];
            $query->where(function ($q) use ($search) {
                $q->where('fullname', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (!empty($_GET['role'])) {
            $query->whereHas('roles', function ($q) {
                $q->where('slug', $_GET['role']);
            });
        }

        if (!empty($_GET['status'])) {
            $status = $_GET['status'];
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $sort = $_GET['sort'] ?? 'created_at';
        $direction = $_GET['direction'] ?? 'desc';

        $validSorts = ['fullname', 'email', 'role', 'created_at'];
        if (in_array($sort, $validSorts)) {
            if ($sort === 'role') {
                $query->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                    ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                    ->orderBy('roles.name', $direction)
                    ->select('users.*');
            } else {
                $query->orderBy($sort, $direction);
            }
        }

        $data = [
            'title' => $this->indexTitle,
            'users' => $query->get(),
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

        if (isset($_POST['roles']) && is_array($_POST['roles'])) {
            $activeRoles = array_keys(array_filter($_POST['roles'], function ($val) {
                return (string) $val === '1' || $val === 'on' || $val === true;
            }));
            $user->roles()->sync($activeRoles);
        } else {
            $user->roles()->sync([]);
        }

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

    #[UseExceptions]
    private function prepareData(array $post, $model = null): array
    {
        $post = $this->normalizeCheckboxes($post);

        if (empty($post['password']) || empty($post['password_confirmation'])) {
            unset($post['password']);
        }

        return $this->buildUpdateData($post, $model, ['fullname', 'email', 'password', 'is_active']);
    }
}
