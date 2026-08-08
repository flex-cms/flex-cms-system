<?php

namespace Flex\Core\Controllers\Api;

use Flex\Attributes\UseExceptions;
use Flex\Core\Controllers\BaseController;
use Flex\Core\Filters\Shared\StatusFilter;
use Flex\Core\Traits\HandlesTableFilters;
use Flex\Models\Role;

class RoleApiController extends BaseController
{
    use HandlesTableFilters;

    #[UseExceptions]
    public function index()
    {
        $query = Role::withCount('permissions');

        $this->applySorting($query);
        $query = $this->applyTableFilters($query);

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($_GET['page_size'] ?? $_GET['per_page'] ?? 10)));

        $totalItems = $query->count();
        $roles = $this->paginateAndFetch($query, $page, $pageSize);

        $this->jsonResponse(true, 'Успешно зареждане', [
            'data' => $this->formatRoles($roles),
            'total' => $totalItems,
            'page' => $page,
            'pageSize' => $pageSize,
        ]);
    }

    private function applySorting($query): void
    {
        $sortKey = $_GET['sort_key'] ?? 'created_at';
        $sortDirection = strtolower($_GET['sort_direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['id', 'name', 'slug', 'priority', 'is_active', 'created_at'];

        if (in_array($sortKey, $allowedSorts, true)) {
            $query->orderBy($sortKey, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }
    }

    private function applyTableFilters($query)
    {
        return $this->applyFilters(
            $query,
            ['name', 'slug'],
            ['name', 'slug', 'created_at'],
            ['status' => StatusFilter::class],
            'name'
        );
    }

    private function paginateAndFetch($query, int $page, int $pageSize)
    {
        return $query->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->get();
    }

    private function formatRoles($roles): array
    {
        return $roles->map(fn($role) => $this->formatRole($role))->toArray();
    }

    private function formatRole($role): array
    {
        return [
            'id' => $role->id,
            'name' => $role->name,
            'slug' => $role->slug,
            'description' => $role->description ?? null,
            'priority' => (int) $role->priority,
            'is_active' => (bool) $role->is_active,
            'is_default' => (bool) $role->is_default,
            'color' => $role->color ?? null,
            'permissions_count' => (int) ($role->permissions_count ?? 0),
            'created_at' => $role->created_at ? $role->created_at->format('Y-m-d H:i') : null,
        ];
    }
}