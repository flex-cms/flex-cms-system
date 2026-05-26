<?php

namespace Flex\Core\Controllers;

use Flex\Core\Routing\View;
use Flex\Models\User;
use Flex\Models\Role;
use Flex\Models\Permission;

class UserController extends BaseController
{
    public function index()
    {
        $currentTab = $_GET['tab'] ?? 'users';

        $usersQuery = User::with('roles');

        if (!empty($_GET['search'])) {
            $search = $_GET['search'];
            $usersQuery->where(function ($query) use ($search) {
                $query->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (!empty($_GET['role'])) {
            $usersQuery->whereHas('roles', function ($query) {
                $query->where('slug', $_GET['role']);
            });
        }

        if (!empty($_GET['status'])) {
            $status = $_GET['status'];
            if ($status === 'active') {
                $usersQuery->where('is_active', true);
            } elseif ($status === 'inactive') {
                $usersQuery->where('is_active', false);
            }
        }

        $sort = $_GET['sort'] ?? 'created_at';
        $direction = $_GET['direction'] ?? 'desc';

        $validSorts = ['username', 'email', 'role', 'created_at'];
        if (in_array($sort, $validSorts)) {
            if ($sort === 'role') {
                $usersQuery->join('user_roles', 'users.id', '=', 'user_roles.user_id')
                    ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                    ->orderBy('roles.name', $direction)
                    ->select('users.*');
            } else {
                $usersQuery->orderBy($sort, $direction);
            }
        }

        $users = $usersQuery->get();
        $roles = Role::orderBy('name', 'asc')->get();
        $permissions = Permission::orderBy('module', 'asc')->get();

        $config = $this->getTabConfig($currentTab);

        $this->render(View::make('admin/users/index', [
            'title' => $config['title'],
            'users' => $users,
            'roles' => $roles,
            'permissions' => $permissions,
            'currentTab' => $currentTab,
            'primaryButton' => $config['button']
        ], 'admin'));
    }

    public function create()
    {
        $allRoles = Role::orderBy('name', 'asc')->get();

        $this->render(View::make('admin/users/create', [
            'title' => 'Създаване на нов потребител',
            'user' => null,
            'allRoles' => $allRoles,
            'assignedRoleIds' => [],
            'backUrl' => '/admin/users/index'
        ], 'admin'));
    }

    public function store()
    {
        $data = $this->getUserDataFromRequest();
        $user = User::create($data);

        if (isset($_POST['roles'])) {
            $user->roles()->sync($_POST['roles']);
        }

        $rolesInput = $_POST['roles'] ?? [];
        $roleIds = array_keys($rolesInput);

        $user->roles()->sync($roleIds);

        View::redirect('/admin/users/edit/' . $user->id);
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        $allRoles = Role::orderBy('name', 'asc')->get();

        $assignedRoleIds = $user->roles()->pluck('roles.id')->toArray();

        $this->render(View::make('admin/users/form', [
            'title' => 'Редактиране на потребител',
            'user' => $user,
            'allRoles' => $allRoles,
            'assignedRoleIds' => $assignedRoleIds,
            'backUrl' => '/admin/users/index'
        ], 'admin'));
    }

    public function update($id)
    {
        $user = User::findOrFail($id);
        $data = $this->getUserDataFromRequest();

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $user->update($data);

        $rolesInput = $_POST['roles'] ?? [];
        $roleIds = array_keys($rolesInput);

        $user->roles()->sync($roleIds);

        View::redirect('/admin/users/edit/' . $id);
    }

    private function getUserDataFromRequest(): array
    {
        return [
            'fullname' => $_POST['fullname'] ?? '',
            'password' => $_POST['password'] ?? '',
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
    }

    public function toggle()
    {
        $this->handleToggleStatus(User::class, 'is_active');
    }

    private function getTabConfig(string $tab): array
    {
        return match ($tab) {
            'roles' => [
                'title' => 'Роли и права',
                'button' => [
                    'label' => 'Нова роля',
                    'url' => '/admin/users/roles/create',
                    'icon' => 'fa-plus'
                ]
            ],
            'permissions' => [
                'title' => 'Системни разрешения',
                'button' => [
                    'label' => 'Ново разрешение',
                    'url' => '/admin/permission/create',
                    'icon' => 'fa-plus'
                ]
            ],
            default => [
                'title' => 'Потребители',
                'button' => [
                    'label' => 'Нов потребител',
                    'url' => '/admin/users/create',
                    'icon' => 'fa-plus'
                ]
            ],
        };
    }
}
