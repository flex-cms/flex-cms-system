<?php

declare(strict_types=1);

namespace Flex\Features\Shopping\Controllers;

use Flex\Core\Assets\AdminAssetRegistry;
use Flex\Core\Helpers\Flash;
use Flex\Core\Http\JsonResponse;
use Flex\Core\Http\Request;
use Flex\Core\View\ViewResponse;
use Flex\Features\AdminUI\Services\AdminUIRenderer;
use Flex\Features\Shopping\Services\CategoryService;
use InvalidArgumentException;

final class CategoryController
{
    public function __construct(
        private readonly CategoryService $categories,
        private readonly AdminUIRenderer $adminUI,
        private readonly AdminAssetRegistry $assets
    ) {
    }

    public function index(): ViewResponse
    {
        $this->registerAssets();

        return $this->adminUI->response(
            'Shopping::categories/index',
            [
                'title' => 'Категории',
            ]
        );
    }

    public function apiIndex(
        Request $request
    ): JsonResponse {
        return new JsonResponse(
            $this->categories->paginate(
                $request->queryAll()
            )
        );
    }

    public function create(): ViewResponse
    {
        $this->registerAssets();

        return $this->adminUI->response(
            'Shopping::categories/create',
            [
                'title' => 'Нова категория',
                'parents' => $this->categories->parentOptions(),
            ]
        );
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $category = $this->categories->create(
                $request->input()
            );

            $message = 'Категорията беше създадена успешно.';

            Flash::success($message);

            return new JsonResponse([
                'success' => true,
                'message' => $message,
                'id' => $category->id,
                'redirect' => '/admin/shopping/categories/'
                    . $category->id
                    . '/edit',
            ]);
        } catch (InvalidArgumentException $exception) {
            return new JsonResponse([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function edit(string $id): ViewResponse
    {
        $this->registerAssets();

        $category = $this->categories->findOrFail(
            (int) $id
        );

        return $this->adminUI->response(
            'Shopping::categories/edit',
            [
                'title' => 'Редактиране на категория',
                'category' => $category,
                'parents' => $this->categories->parentOptions(
                    $category->id
                ),
            ]
        );
    }

    public function update(
        Request $request,
        string $id
    ): JsonResponse {
        try {
            $category = $this->categories->update(
                (int) $id,
                $request->input()
            );

            $message = 'Категорията беше обновена успешно.';

            return new JsonResponse([
                'success' => true,
                'message' => $message,
                'id' => $category->id,
            ]);
        } catch (InvalidArgumentException $exception) {
            return new JsonResponse([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function toggle(string $id): JsonResponse
    {
        $category = $this->categories->toggle(
            (int) $id
        );

        return new JsonResponse([
            'success' => true,
            'message' => $category->is_active
                ? 'Категорията беше активирана.'
                : 'Категорията беше деактивирана.',
            'is_active' => $category->is_active,
        ]);
    }

    public function delete(string $id): JsonResponse
    {
        $this->categories->delete(
            (int) $id
        );

        return new JsonResponse([
            'success' => true,
            'message' => 'Категорията беше преместена в кошчето.',
        ]);
    }

    public function restore(string $id): JsonResponse
    {
        $this->categories->restore(
            (int) $id
        );

        return new JsonResponse([
            'success' => true,
            'message' => 'Категорията беше възстановена.',
        ]);
    }

    public function forceDelete(string $id): JsonResponse
    {
        $this->categories->forceDelete(
            (int) $id
        );

        return new JsonResponse([
            'success' => true,
            'message' => 'Категорията беше изтрита завинаги.',
        ]);
    }

    public function bulk(
        Request $request
    ): JsonResponse {
        try {
            $result = $this->categories->bulk(
                $request->json()
            );

            return new JsonResponse([
                'success' => true,
                'message' => $result['message'],
                'affected' => $result['affected'],
            ]);
        } catch (InvalidArgumentException $exception) {
            return new JsonResponse([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    private function registerAssets(): void
    {
        $this->assets->script(
            'Shopping',
            'shopping'
        );
    }
}
