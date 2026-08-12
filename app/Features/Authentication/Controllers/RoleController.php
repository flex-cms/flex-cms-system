<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Controllers;

use Flex\Core\Assets\AdminAssetRegistry;
use Flex\Core\Helpers\Flash;
use Flex\Core\Http\JsonResponse;
use Flex\Core\Http\Request;
use Flex\Core\View\ViewResponse;
use Flex\Features\AdminUI\Services\AdminUIRenderer;
use Flex\Features\Authentication\Models\Permission;
use Flex\Features\Authentication\Services\RoleService;
use InvalidArgumentException;

final class RoleController
{
    public function __construct(
        private readonly RoleService $roles,
        private readonly AdminUIRenderer $adminUI,
        private readonly AdminAssetRegistry $assets
    ) {
    }

    public function index(): ViewResponse
    {
        $this->registerAssets();

        return $this->adminUI->response(
            'Authentication::roles/index',
            ['title' => 'Роли']
        );
    }

    public function apiIndex(Request $request): JsonResponse
    {
        return new JsonResponse(
            $this->roles->paginate($request->queryAll())
        );
    }

    public function create(): ViewResponse
    {
        $this->registerAssets();

        return $this->adminUI->response(
            'Authentication::roles/create',
            [
                'title' => 'Нова роля',
                'role' => null,
                'permissions' => $this->permissionOptions(),
            ]
        );
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $role = $this->roles->create($request->input());
            $message = 'Ролята беше създадена успешно.';

            Flash::success($message);

            return new JsonResponse([
                'success' => true,
                'message' => $message,
                'id' => $role->id,
                'redirect' => '/admin/authentication/roles/'
                    . $role->id
                    . '/edit',
            ]);
        } catch (InvalidArgumentException $exception) {
            return $this->validationError($exception);
        }
    }

    public function edit(string $id): ViewResponse
    {
        $this->registerAssets();

        return $this->adminUI->response(
            'Authentication::roles/edit',
            [
                'title' => 'Редактиране на роля',
                'role' => $this->roles->findOrFail((int) $id),
                'permissions' => $this->permissionOptions(),
            ]
        );
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $role = $this->roles->update(
                (int) $id,
                $request->input()
            );

            return new JsonResponse([
                'success' => true,
                'message' => 'Ролята беше обновена успешно.',
                'id' => $role->id,
            ]);
        } catch (InvalidArgumentException $exception) {
            return $this->validationError($exception);
        }
    }

    public function toggle(string $id): JsonResponse
    {
        try {
            $role = $this->roles->toggle((int) $id);

            return new JsonResponse([
                'success' => true,
                'message' => $role->is_active
                    ? 'Ролята беше активирана.'
                    : 'Ролята беше деактивирана.',
                'is_active' => $role->is_active,
            ]);
        } catch (InvalidArgumentException $exception) {
            return $this->validationError($exception);
        }
    }

    public function delete(string $id): JsonResponse
    {
        return $this->operation(
            fn() => $this->roles->delete((int) $id),
            'Ролята беше преместена в кошчето.'
        );
    }

    public function restore(string $id): JsonResponse
    {
        return $this->operation(
            fn() => $this->roles->restore((int) $id),
            'Ролята беше възстановена.'
        );
    }

    public function forceDelete(string $id): JsonResponse
    {
        return $this->operation(
            fn() => $this->roles->forceDelete((int) $id),
            'Ролята беше изтрита завинаги.'
        );
    }

    public function bulk(Request $request): JsonResponse
    {
        try {
            $result = $this->roles->bulk($request->json());

            return new JsonResponse([
                'success' => true,
                'message' => $result['message'],
                'affected' => $result['affected'],
            ]);
        } catch (InvalidArgumentException $exception) {
            return $this->validationError($exception);
        }
    }

    private function permissionOptions(): iterable
    {
        return Permission::query()
            ->where('is_active', true)
            ->orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');
    }

    private function operation(callable $operation, string $message): JsonResponse
    {
        try {
            $operation();

            return new JsonResponse([
                'success' => true,
                'message' => $message,
            ]);
        } catch (InvalidArgumentException $exception) {
            return $this->validationError($exception);
        }
    }

    private function validationError(\Throwable $exception): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'message' => $exception->getMessage(),
        ], 422);
    }

    private function registerAssets(): void
    {
        $this->assets->script('Authentication', 'authentication');
    }
}
