<?php

declare(strict_types=1);

namespace Flex\Features\Shopping\Controllers;

use Flex\Core\Assets\AdminAssetRegistry;
use Flex\Core\Helpers\Flash;
use Flex\Core\Http\JsonResponse;
use Flex\Core\Http\Request;
use Flex\Core\View\ViewResponse;
use Flex\Features\AdminUI\Services\AdminUIRenderer;
use Flex\Features\Shopping\Services\ProductService;
use InvalidArgumentException;

final class ProductController
{
    public function __construct(
        private readonly ProductService $products,
        private readonly AdminUIRenderer $adminUI,
        private readonly AdminAssetRegistry $assets
    ) {
    }

    public function index(): ViewResponse
    {
        $this->registerAssets();

        return $this->adminUI->response('Shopping::products/index', [
            'title' => 'Продукти',
        ]);
    }

    public function apiIndex(Request $request): JsonResponse
    {
        return new JsonResponse($this->products->paginate($request->queryAll()));
    }

    public function create(): ViewResponse
    {
        $this->registerAssets();

        return $this->adminUI->response('Shopping::products/create', [
            'title' => 'Нов продукт',
            'categories' => $this->products->categoryOptions(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        return $this->save(fn() => $this->products->create($request->input()), true);
    }

    public function edit(string $id): ViewResponse
    {
        $this->registerAssets();

        return $this->adminUI->response('Shopping::products/edit', [
            'title' => 'Редактиране на продукт',
            'product' => $this->products->findOrFail((int) $id),
            'categories' => $this->products->categoryOptions(),
        ]);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        return $this->save(
            fn() => $this->products->update((int) $id, $request->input())
        );
    }

    public function toggle(string $id): JsonResponse
    {
        try {
            $product = $this->products->toggle((int) $id);

            return new JsonResponse([
                'success' => true,
                'message' => $product->status === 'published'
                    ? 'Продуктът беше публикуван.'
                    : 'Продуктът беше върнат като чернова.',
            ]);
        } catch (InvalidArgumentException $exception) {
            return $this->validationError($exception);
        }
    }

    public function delete(string $id): JsonResponse
    {
        $this->products->delete((int) $id);

        return new JsonResponse(['success' => true, 'message' => 'Продуктът беше преместен в кошчето.']);
    }

    public function restore(string $id): JsonResponse
    {
        $this->products->restore((int) $id);

        return new JsonResponse(['success' => true, 'message' => 'Продуктът беше възстановен.']);
    }

    public function forceDelete(string $id): JsonResponse
    {
        try {
            $this->products->forceDelete((int) $id);

            return new JsonResponse(['success' => true, 'message' => 'Продуктът беше изтрит завинаги.']);
        } catch (InvalidArgumentException $exception) {
            return $this->validationError($exception);
        }
    }

    public function bulk(Request $request): JsonResponse
    {
        try {
            $result = $this->products->bulk($request->json());

            return new JsonResponse(['success' => true] + $result);
        } catch (InvalidArgumentException $exception) {
            return $this->validationError($exception);
        }
    }

    private function save(callable $operation, bool $creating = false): JsonResponse
    {
        try {
            $product = $operation();
            $message = $creating
                ? 'Продуктът беше създаден успешно.'
                : 'Продуктът беше обновен успешно.';

            if ($creating) {
                Flash::success($message);
            }

            return new JsonResponse([
                'success' => true,
                'message' => $message,
                'id' => $product->id,
                'redirect' => $creating
                    ? '/admin/shopping/products/' . $product->id . '/edit'
                    : null,
            ]);
        } catch (InvalidArgumentException $exception) {
            return $this->validationError($exception);
        }
    }

    private function validationError(InvalidArgumentException $exception): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'message' => $exception->getMessage(),
        ], 422);
    }

    private function registerAssets(): void
    {
        $this->assets->script('Shopping', 'shopping');
    }
}
