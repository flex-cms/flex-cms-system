<?php

declare(strict_types=1);

namespace Flex\Features\Shopping\Repositories;

use Flex\Features\Shopping\Models\Product;
use Illuminate\Database\Eloquent\Collection;

final class EloquentProductRepository implements ProductRepositoryInterface
{
    private const SORTABLE_COLUMNS = [
        'id', 'name', 'sku', 'price', 'stock_quantity',
        'stock_status', 'status', 'is_featured',
        'created_at', 'updated_at',
    ];

    public function findOrFail(int $id): Product
    {
        return Product::query()
            ->withTrashed()
            ->with(['categories', 'images', 'variants'])
            ->findOrFail($id);
    }

    public function paginate(
        int $page,
        int $perPage,
        ?string $sortBy = null,
        ?string $sortDirection = null,
        ?string $search = null,
        array $filters = []
    ): array {
        $page = max(1, $page);
        $perPage = max(1, min(250, $perPage));
        $sortBy = in_array($sortBy, self::SORTABLE_COLUMNS, true)
            ? $sortBy
            : 'updated_at';
        $sortDirection = $sortDirection === 'asc' ? 'asc' : 'desc';

        $query = Product::query()
            ->with(['categories:id,name', 'primaryImage'])
            ->withCount('variants');

        $search = trim((string) $search);

        if ($search !== '') {
            $query->where(static function ($query) use ($search): void {
                $query
                    ->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('slug', 'LIKE', '%' . $search . '%')
                    ->orWhere('sku', 'LIKE', '%' . $search . '%')
                    ->orWhereHas(
                        'categories',
                        static fn($query) => $query
                            ->where('name', 'LIKE', '%' . $search . '%')
                    );
            });
        }

        $status = $filters['status'] ?? null;

        if ($status === 'deleted') {
            $query->onlyTrashed();
        } elseif (in_array($status, ['draft', 'published', 'archived'], true)) {
            $query->where('status', $status);
        }

        $stock = $filters['stock'] ?? null;

        if (in_array($stock, ['in_stock', 'out_of_stock', 'backorder'], true)) {
            $query->where('stock_status', $stock);
        }

        $total = (clone $query)->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min($page, $lastPage);

        $rows = $query
            ->orderBy($sortBy, $sortDirection)
            ->orderBy('id')
            ->forPage($page, $perPage)
            ->get();

        return [
            'data' => $rows->map(
                static fn(Product $product): array => [
                    'id' => (int) $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'sku' => $product->sku,
                    'price' => (string) $product->price,
                    'compare_price' => $product->compare_price,
                    'manage_stock' => (bool) $product->manage_stock,
                    'stock_quantity' => (int) $product->stock_quantity,
                    'stock_status' => $product->stock_status,
                    'status' => $product->status,
                    'is_featured' => (bool) $product->is_featured,
                    'categories' => $product->categories
                        ->pluck('name')->implode(', '),
                    'primary_image' => $product->primaryImage?->path,
                    'variants_count' => (int) $product->variants_count,
                    'created_at' => $product->created_at?->toAtomString(),
                    'updated_at' => $product->updated_at?->toAtomString(),
                    'deleted_at' => $product->deleted_at?->toAtomString(),
                ]
            )->values()->all(),
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => $lastPage,
            ],
        ];
    }

    public function create(array $data): Product
    {
        return Product::query()->create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->fill($data);
        $product->save();

        return $product->refresh();
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    public function restore(Product $product): void
    {
        $product->restore();
    }

    public function forceDelete(Product $product): void
    {
        $product->forceDelete();
    }

    public function transaction(callable $callback): mixed
    {
        return (new Product())->getConnection()->transaction($callback);
    }

    public function bulkSetStatus(array $ids, string $status): int
    {
        return Product::query()->whereIn('id', $ids)->update([
            'status' => $status,
        ]);
    }

    public function bulkDelete(array $ids): int
    {
        return $this->applyToMany(
            Product::query()->whereIn('id', $ids)->get(),
            static fn(Product $product): bool => (bool) $product->delete()
        );
    }

    public function bulkRestore(array $ids): int
    {
        return $this->applyToMany(
            Product::query()->onlyTrashed()->whereIn('id', $ids)->get(),
            static fn(Product $product): bool => (bool) $product->restore()
        );
    }

    public function bulkForceDelete(array $ids): int
    {
        return $this->applyToMany(
            Product::query()->onlyTrashed()->whereIn('id', $ids)->get(),
            static fn(Product $product): bool => (bool) $product->forceDelete()
        );
    }

    private function applyToMany(Collection $products, callable $action): int
    {
        $affected = 0;

        foreach ($products as $product) {
            if ($action($product)) {
                $affected++;
            }
        }

        return $affected;
    }
}
