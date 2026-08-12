<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Controllers;

use Flex\Core\Http\RedirectResponse;
use Flex\Core\Http\Request;
use Flex\Core\View\ViewResponse;
use Flex\Features\AdminUI\Services\AdminUIRenderer;
use Flex\Features\Authentication\Models\Permission;
use Flex\Features\Authentication\Models\Role;
use Flex\Features\Authentication\Repositories\RoleRepositoryInterface;
use Flex\Features\Authentication\Services\RoleService;

final readonly class RoleController
{
    public function __construct(private RoleRepositoryInterface $roles, private RoleService $service, private AdminUIRenderer $adminUI) {}
    public function index(): ViewResponse { return $this->adminUI->response('Authentication::roles/index', ['title' => 'Роли', 'roles' => $this->roles->all()]); }
    public function create(): ViewResponse { return $this->form(null); }
    public function edit(int $id): ViewResponse { return $this->form($this->required($id)); }
    public function store(Request $request): RedirectResponse { $role = $this->service->save(null, $request->input()); return new RedirectResponse('/admin/authentication/roles/' . $role->id . '/edit'); }
    public function update(Request $request, int $id): RedirectResponse { $this->service->save($this->required($id), $request->input()); return new RedirectResponse('/admin/authentication/roles/' . $id . '/edit'); }
    public function delete(int $id): RedirectResponse { $role = $this->required($id); if ($role->users()->exists()) { throw new \DomainException('Ролята се използва от потребители.'); } $role->delete(); return new RedirectResponse('/admin/authentication/roles'); }
    private function form(?Role $role): ViewResponse { return $this->adminUI->response('Authentication::roles/form', ['title' => $role ? 'Редактиране на роля' : 'Нова роля', 'role' => $role, 'permissions' => Permission::query()->where('is_active', true)->orderBy('module')->orderBy('name')->get()->groupBy('module')]); }
    private function required(int $id): Role { return $this->roles->find($id) ?? throw new \RuntimeException('Ролята не е намерена.'); }
}
