<?php

declare(strict_types=1);

namespace Flex\Features\Shopping\Services;

use Flex\Core\Support\Slug;
use Flex\Features\Shopping\Models\Category;
use Flex\Features\Shopping\Models\Product;
use Flex\Features\Shopping\Models\ProductImage;
use Flex\Features\Shopping\Repositories\ProductRepositoryInterface;
use InvalidArgumentException;

final readonly class ProductService
{
    private const STATUSES = ['draft', 'published', 'archived'];
    private const STOCK_STATUSES = ['in_stock', 'out_of_stock', 'backorder'];

    public function __construct(
        private ProductRepositoryInterface $products
    ) {
    }

    public function paginate(array $input): array
    {
        $page = max(1, (int) ($input['page'] ?? 1));
        $perPage = (int) ($input['per_page'] ?? 25);

        if (!in_array($perPage, [10, 20, 25, 50, 100], true)) {
            $perPage = 25;
        }

        $direction = strtolower((string) ($input['direction'] ?? ''));

        return $this->products->paginate(
            page: $page,
            perPage: $perPage,
            sortBy: isset($input['sort'])
                ? trim((string) $input['sort'])
                : null,
            sortDirection: in_array($direction, ['asc', 'desc'], true)
                ? $direction
                : null,
            search: isset($input['search'])
                ? trim((string) $input['search'])
                : null,
            filters: isset($input['filter']) && is_array($input['filter'])
                ? $input['filter']
                : []
        );
    }

    public function findOrFail(int $id): Product
    {
        return $this->products->findOrFail($id);
    }

    public function categoryOptions(): iterable
    {
        return Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function create(array $input): Product
    {
        return $this->save(null, $input);
    }

    public function update(int $id, array $input): Product
    {
        $product = $this->findOrFail($id);

        if ($product->trashed()) {
            throw new InvalidArgumentException(
                'Изтрит продукт не може да бъде редактиран.'
            );
        }

        return $this->save($product, $input);
    }

    public function toggle(int $id): Product
    {
        $product = $this->findOrFail($id);

        if ($product->trashed()) {
            throw new InvalidArgumentException(
                'Статусът на изтрит продукт не може да бъде променян.'
            );
        }

        return $this->products->update($product, [
            'status' => $product->status === 'published'
                ? 'draft'
                : 'published',
        ]);
    }

    public function delete(int $id): void
    {
        $this->products->delete($this->findOrFail($id));
    }

    public function restore(int $id): void
    {
        $product = $this->findOrFail($id);

        if ($product->trashed()) {
            $this->products->restore($product);
        }
    }

    public function forceDelete(int $id): void
    {
        $product = $this->findOrFail($id);

        if (!$product->trashed()) {
            throw new InvalidArgumentException(
                'Продуктът трябва първо да бъде преместен в кошчето.'
            );
        }

        $this->products->forceDelete($product);
    }

    public function bulk(array $input): array
    {
        $action = trim((string) ($input['action'] ?? ''));
        $ids = array_values(array_unique(array_filter(
            array_map('intval', is_array($input['ids'] ?? null)
                ? $input['ids']
                : []),
            static fn(int $id): bool => $id > 0
        )));

        if ($ids === []) {
            throw new InvalidArgumentException(
                'Не са избрани валидни продукти.'
            );
        }

        $affected = match ($action) {
            'publish' => $this->products->bulkSetStatus($ids, 'published'),
            'draft' => $this->products->bulkSetStatus($ids, 'draft'),
            'archive' => $this->products->bulkSetStatus($ids, 'archived'),
            'trash' => $this->products->bulkDelete($ids),
            'restore' => $this->products->bulkRestore($ids),
            'force-delete' => $this->products->bulkForceDelete($ids),
            default => throw new InvalidArgumentException(
                'Невалидно групово действие.'
            ),
        };

        $stockQuantity = (int) ($input['stock_quantity'] ?? 0);

        if ($stockQuantity < 0) {
            throw new InvalidArgumentException(
                'Количеството не може да бъде отрицателно.'
            );
        }

        return [
            'affected' => $affected,
            'message' => sprintf('Обработени продукти: %d.', $affected),
        ];
    }

    private function save(?Product $product, array $input): Product
    {
        $data = $this->normalize($input, $product);
        $categoryIds = $this->normalizeCategoryIds(
            $input['categories'] ?? []
        );
        $image = $this->nullableString($input['primary_image'] ?? null);
        $imageAlt = $this->nullableString($input['primary_image_alt'] ?? null);

        return $this->products->transaction(
            function () use (
                $product,
                $data,
                $categoryIds,
                $image,
                $imageAlt
            ): Product {
                $saved = $product === null
                    ? $this->products->create($data)
                    : $this->products->update($product, $data);

                $saved->categories()->sync($categoryIds);
                $this->syncPrimaryImage($saved, $image, $imageAlt);

                // TODO: Добавете отделен editor за галерия, подреждане
                // на изображения и продуктови варианти по атрибути.

                return $saved->load([
                    'categories', 'images', 'variants',
                ]);
            }
        );
    }

    private function normalize(array $input, ?Product $product): array
    {
        $name = trim((string) ($input['name'] ?? ''));

        if ($name === '') {
            throw new InvalidArgumentException(
                'Името на продукта е задължително.'
            );
        }

        $slugInput = trim((string) ($input['slug'] ?? ''));
        $slug = $this->uniqueSlug(
            $slugInput === '' ? Slug::make($name) : Slug::make($slugInput),
            $product?->id
        );

        $sku = $this->nullableString($input['sku'] ?? null);

        if ($sku !== null) {
            $duplicateSku = Product::query()
                ->withTrashed()
                ->where('sku', $sku)
                ->when(
                    $product !== null,
                    static fn($query) => $query->whereKeyNot($product->id)
                )
                ->exists();

            if ($duplicateSku) {
                throw new InvalidArgumentException(
                    'Вече има продукт с този SKU, включително в кошчето.'
                );
            }
        }

        $status = (string) ($input['status'] ?? 'draft');
        $stockStatus = (string) ($input['stock_status'] ?? 'in_stock');

        if (!in_array($status, self::STATUSES, true)) {
            throw new InvalidArgumentException('Невалиден продуктов статус.');
        }

        if (!in_array($stockStatus, self::STOCK_STATUSES, true)) {
            throw new InvalidArgumentException('Невалиден складов статус.');
        }

        $price = $this->decimal($input['price'] ?? 0, 'Цена', false);
        $comparePrice = $this->decimal(
            $input['compare_price'] ?? null,
            'Стара цена'
        );

        if ($comparePrice !== null && $comparePrice <= $price) {
            throw new InvalidArgumentException(
                'Старата цена трябва да бъде по-висока от продажната цена.'
            );
        }

        return [
            'name' => $name,
            'slug' => $slug,
            'sku' => $sku,
            'short_description' => $this->nullableString(
                $input['short_description'] ?? null
            ),
            'description' => $this->nullableString(
                $input['description'] ?? null
            ),
            'price' => $price,
            'compare_price' => $comparePrice,
            'cost_price' => $this->decimal(
                $input['cost_price'] ?? null,
                'Доставна цена'
            ),
            'manage_stock' => $this->toBoolean(
                $input['manage_stock'] ?? false
            ),
            'stock_quantity' => $stockQuantity,
            'stock_status' => $stockStatus,
            'weight' => $this->decimal($input['weight'] ?? null, 'Тегло'),
            'length' => $this->decimal($input['length'] ?? null, 'Дължина'),
            'width' => $this->decimal($input['width'] ?? null, 'Ширина'),
            'height' => $this->decimal($input['height'] ?? null, 'Височина'),
            'status' => $status,
            'is_featured' => $this->toBoolean(
                $input['is_featured'] ?? false
            ),
            'meta_title' => $this->nullableString(
                $input['meta_title'] ?? null
            ),
            'meta_description' => $this->nullableString(
                $input['meta_description'] ?? null
            ),
        ];
    }

    private function normalizeCategoryIds(mixed $categories): array
    {
        if (!is_array($categories)) {
            return [];
        }

        $ids = array_is_list($categories)
            ? array_map('intval', $categories)
            : array_map('intval', array_keys(array_filter(
                $categories,
                fn(mixed $value): bool => $this->toBoolean($value)
            )));

        $ids = array_values(array_unique(array_filter(
            $ids,
            static fn(int $id): bool => $id > 0
        )));

        $valid = Category::query()
            ->where('is_active', true)
            ->whereIn('id', $ids)
            ->pluck('id')
            ->map(static fn($id): int => (int) $id)
            ->values()->all();

        if (count($valid) !== count($ids)) {
            throw new InvalidArgumentException(
                'Избрана е невалидна или неактивна категория.'
            );
        }

        return $valid;
    }

    private function syncPrimaryImage(
        Product $product,
        ?string $path,
        ?string $alt
    ): void {
        if ($path === null) {
            $product->images()->where('is_primary', true)->delete();

            return;
        }

        ProductImage::query()->updateOrCreate(
            ['product_id' => $product->id, 'is_primary' => true],
            ['path' => $path, 'alt' => $alt, 'sort_order' => 0]
        );
    }

    private function uniqueSlug(string $base, ?int $ignoreId): string
    {
        $candidate = $base;
        $counter = 2;

        while (true) {
            $query = Product::query()->withTrashed()->where('slug', $candidate);

            if ($ignoreId !== null) {
                $query->whereKeyNot($ignoreId);
            }

            if (!$query->exists()) {
                return $candidate;
            }

            $candidate = $base . '-' . $counter++;
        }
    }

    private function decimal(
        mixed $value,
        string $label,
        bool $nullable = true
    ): ?float {
        if (($value === '' || $value === null) && $nullable) {
            return null;
        }

        if (!is_numeric($value) || (float) $value < 0) {
            throw new InvalidArgumentException(
                $label . ' трябва да бъде положително число.'
            );
        }

        return round((float) $value, 3);
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    private function toBoolean(mixed $value): bool
    {
        return is_bool($value)
            ? $value
            : in_array(strtolower((string) $value), [
                '1', 'true', 'yes', 'on',
            ], true);
    }
}
