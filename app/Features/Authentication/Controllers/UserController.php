<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Controllers;

use Flex\Core\Http\RedirectResponse;
use Flex\Core\Http\Request;
use Flex\Core\View\ViewResponse;
use Flex\Features\AdminUI\Services\AdminUIRenderer;
use Flex\Features\Authentication\Models\Role;
use Flex\Features\Authentication\Models\User;
use Flex\Features\Authentication\Repositories\UserRepositoryInterface;
use Flex\Features\Authentication\Services\AuthorizationService;
use Flex\Features\Authentication\Services\UserService;

final readonly class UserController
{
    public function __construct(private UserRepositoryInterface $users, private UserService $service, private AuthorizationService $authorization, private AdminUIRenderer $adminUI) {}
    public function index(): ViewResponse { return $this->adminUI->response('Authentication::users/index', ['title' => 'Потребители', 'users' => $this->users->all()]); }
    public function create(): ViewResponse { return $this->form(null); }
    public function edit(int $id): ViewResponse { return $this->form($this->required($id)); }
    public function store(Request $request): RedirectResponse { $user = $this->service->save(null, $request->input()); return new RedirectResponse('/admin/authentication/users/' . $user->id . '/edit'); }
    public function update(Request $request, int $id): RedirectResponse { $this->service->save($this->required($id), $request->input()); return new RedirectResponse('/admin/authentication/users/' . $id . '/edit'); }
    public function delete(int $id): RedirectResponse { $this->service->delete($this->required($id), $this->authorization->currentUser()); return new RedirectResponse('/admin/authentication/users'); }
    private function form(?User $user): ViewResponse { return $this->adminUI->response('Authentication::users/form', ['title' => $user ? 'Редактиране на потребител' : 'Нов потребител', 'user' => $user, 'roles' => Role::query()->where('is_active', true)->orderBy('name')->get()]); }
    private function required(int $id): User { return $this->users->find($id) ?? throw new \RuntimeException('Потребителят не е намерен.'); }
}
