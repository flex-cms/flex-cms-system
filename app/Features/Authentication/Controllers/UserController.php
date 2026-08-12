<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Controllers;

use DomainException;
use Flex\Core\Assets\AdminAssetRegistry;
use Flex\Core\Helpers\Flash;
use Flex\Core\Http\JsonResponse;
use Flex\Core\Http\Request;
use Flex\Core\View\ViewResponse;
use Flex\Features\AdminUI\Services\AdminUIRenderer;
use Flex\Features\Authentication\Models\Role;
use Flex\Features\Authentication\Services\AuthorizationService;
use Flex\Features\Authentication\Services\UserService;
use InvalidArgumentException;

final class UserController
{
    public function __construct(
        private readonly UserService $users,
        private readonly AuthorizationService $authorization,
        private readonly AdminUIRenderer $adminUI,
        private readonly AdminAssetRegistry $assets
    ) {
    }

    public function index(): ViewResponse
    {
        $this->registerAssets();

        return $this->adminUI->response(
            'Authentication::users/index',
            [
                'title' => 'Потребители',
            ]
        );
    }

    public function apiIndex(
        Request $request
    ): JsonResponse {
        return new JsonResponse(
            $this->users->paginate(
                $request->queryAll(),
                $this->authorization->currentUser()
            )
        );
    }

    public function create(): ViewResponse
    {
        $this->registerAssets();

        return $this->adminUI->response(
            'Authentication::users/create',
            [
                'title' => 'Нов потребител',
                'user' => null,
                'roles' => $this->roleOptions(),
            ]
        );
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $user = $this->users->create(
                $request->input()
            );

            $message = 'Потребителят беше създаден успешно.';

            Flash::success($message);

            return new JsonResponse([
                'success' => true,
                'message' => $message,
                'id' => $user->id,
                'redirect' => '/admin/authentication/users/'
                    . $user->id
                    . '/edit',
            ]);
        } catch (InvalidArgumentException|DomainException $exception) {
            return $this->validationError($exception);
        }
    }

    public function edit(string $id): ViewResponse
    {
        $this->registerAssets();

        $user = $this->users->findOrFail(
            (int) $id
        );

        return $this->adminUI->response(
            'Authentication::users/edit',
            [
                'title' => 'Редактиране на потребител',
                'user' => $user,
                'roles' => $this->roleOptions(),
            ]
        );
    }

    public function update(
        Request $request,
        string $id
    ): JsonResponse {
        try {
            $user = $this->users->update(
                (int) $id,
                $request->input(),
                $this->authorization->currentUser()
            );

            return new JsonResponse([
                'success' => true,
                'message' => 'Потребителят беше обновен успешно.',
                'id' => $user->id,
            ]);
        } catch (InvalidArgumentException|DomainException $exception) {
            return $this->validationError($exception);
        }
    }

    public function toggle(string $id): JsonResponse
    {
        try {
            $user = $this->users->toggle(
                (int) $id,
                $this->authorization->currentUser()
            );

            return new JsonResponse([
                'success' => true,
                'message' => $user->is_active
                    ? 'Потребителят беше активиран.'
                    : 'Потребителят беше деактивиран.',
                'is_active' => $user->is_active,
            ]);
        } catch (InvalidArgumentException $exception) {
            return $this->validationError($exception);
        }
    }

    public function delete(string $id): JsonResponse
    {
        try {
            $this->users->delete(
                (int) $id,
                $this->authorization->currentUser()
            );

            return new JsonResponse([
                'success' => true,
                'message' => 'Потребителят беше преместен в кошчето.',
            ]);
        } catch (InvalidArgumentException $exception) {
            return $this->validationError($exception);
        }
    }

    public function restore(string $id): JsonResponse
    {
        try {
            $this->users->restore(
                (int) $id
            );

            return new JsonResponse([
                'success' => true,
                'message' => 'Потребителят беше възстановен.',
            ]);
        } catch (InvalidArgumentException $exception) {
            return $this->validationError($exception);
        }
    }

    public function forceDelete(string $id): JsonResponse
    {
        try {
            $this->users->forceDelete(
                (int) $id,
                $this->authorization->currentUser()
            );

            return new JsonResponse([
                'success' => true,
                'message' => 'Потребителят беше изтрит завинаги.',
            ]);
        } catch (InvalidArgumentException $exception) {
            return $this->validationError($exception);
        }
    }

    public function bulk(Request $request): JsonResponse
    {
        try {
            $result = $this->users->bulk(
                $request->json(),
                $this->authorization->currentUser()
            );

            return new JsonResponse([
                'success' => true,
                'message' => $result['message'],
                'affected' => $result['affected'],
            ]);
        } catch (InvalidArgumentException $exception) {
            return $this->validationError($exception);
        }
    }

    private function roleOptions(): iterable
    {
        return Role::query()
            ->where('is_active', true)
            ->orderBy('priority')
            ->orderBy('name')
            ->get();
    }

    private function validationError(
        \Throwable $exception
    ): JsonResponse {
        return new JsonResponse([
            'success' => false,
            'message' => $exception->getMessage(),
        ], 422);
    }

    private function registerAssets(): void
    {
        $this->assets->script(
            'Authentication',
            'authentication'
        );
    }
}
