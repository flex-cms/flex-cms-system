<?php

namespace Flex\Core\Controllers\Api;

use Flex\Attributes\UseExceptions;
use Flex\Core\Controllers\BaseController;
use Flex\Core\Filters\Shared\StatusFilter;
use Flex\Core\Traits\HandlesTableFilters;
use Flex\Models\Permission;

class PermissionApiController extends BaseController
{
    use HandlesTableFilters;

    #[UseExceptions]
    public function index()
    {
        $query = Permission::withCount('roles');

        $this->applyModuleFilter($query);
        $this->applySorting($query);
        $query = $this->applyTableFilters($query);

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($_GET['page_size'] ?? $_GET['per_page'] ?? 10)));

        $totalItems = $query->count();
        $permissions = $this->paginateAndFetch($query, $page, $pageSize);

        $modules = Permission::whereNotNull('module')
            ->where('module', '!=', '')
            ->distinct()
            ->pluck('module')
            ->values()
            ->toArray();

        $this->jsonResponse(true, 'Успешно зареждане', [
            'data' => $this->formatPermissions($permissions),
            'total' => $totalItems,
            'page' => $page,
            'pageSize' => $pageSize,
            'modules' => $modules,
        ]);
    }

    private function applyModuleFilter($query): void
    {
        if (!empty($_GET['module'])) {
            $query->where('module', $_GET['module']);
        }
    }

    private function applySorting($query): void
    {
        $sortKey = $_GET['sort_key'] ?? 'created_at';
        $sortDirection = strtolower($_GET['sort_direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['id', 'name', 'slug', 'module', 'is_active', 'created_at'];

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

    private function formatPermissions($permissions): array
    {
        return $permissions->map(fn($permission) => $this->formatPermission($permission))->toArray();
    }

    private function formatPermission($permission): array
    {
        return [
            'id' => $permission->id,
            'name' => $permission->name,
            'slug' => $permission->slug,
            'module' => $permission->module ?? null,
            'description' => $permission->description ?? null,
            'is_active' => (bool) $permission->is_active,
            'roles_count' => (int) ($permission->roles_count ?? 0),
            'created_at' => $permission->created_at ? $permission->created_at->format('Y-m-d H:i') : null,
        ];
    }
}
