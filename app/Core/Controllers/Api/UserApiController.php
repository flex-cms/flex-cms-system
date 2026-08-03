<?php

namespace Flex\Core\Controllers\Api;

use Flex\Attributes\UseExceptions;
use Flex\Core\Controllers\BaseController;
use Flex\Core\Filters\Shared\StatusFilter;
use Flex\Core\Traits\HandlesTableFilters;
use Flex\Models\User;

class UserApiController extends BaseController
{
    use HandlesTableFilters;

    #[UseExceptions]
    public function index()
    {
        $query = User::with('roles');

        $this->applyRoleFilter($query);
        $this->applySorting($query);
        $this->applyTableFilters($query);

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $pageSize = max(1, min(100, (int) ($_GET['page_size'] ?? $_GET['per_page'] ?? 10)));

        $totalItems = $query->count();
        $users = $this->paginateAndFetch($query, $page, $pageSize);

        $this->jsonResponse(true, 'Успешно зареждане', [
            'data' => $this->formatUsers($users),
            'total' => $totalItems,
            'page' => $page,
            'pageSize' => $pageSize,
        ]);
    }

    private function applyRoleFilter($query): void
    {
        if (empty($_GET['role'])) {
            return;
        }

        $roleParam = $_GET['role'];

        $query->whereHas('roles', function ($q) use ($roleParam) {
            if (is_numeric($roleParam)) {
                $q->where('roles.id', $roleParam);
            } else {
                $q->where('roles.slug', $roleParam);
            }
        });
    }

    private function applySorting($query): void
    {
        $sortKey = $_GET['sort_key'] ?? 'created_at';
        $sortDirection = strtolower($_GET['sort_direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['id', 'fullname', 'email', 'created_at', 'is_active'];

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
            ['fullname', 'email'],
            ['fullname', 'email', 'created_at'],
            ['status' => StatusFilter::class],
            'fullname'
        );
    }

    private function paginateAndFetch($query, int $page, int $pageSize)
    {
        return $query->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->get();
    }

    private function formatUsers($users): array
    {
        return $users->map(fn($user) => $this->formatUser($user))->toArray();
    }

    private function formatUser($user): array
    {
        return [
            'id' => $user->id,
            'fullname' => $user->fullname,
            'email' => $user->email,
            'is_active' => (bool) $user->is_active,
            'roles' => $user->roles->pluck('name')->toArray(),
            'created_at' => $user->created_at ? $user->created_at->format('Y-m-d H:i') : null,
        ];
    }
}
