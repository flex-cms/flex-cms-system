<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Repositories;

use Flex\Features\Authentication\Models\User;

final class EloquentUserRepository implements UserRepositoryInterface
{
    public function all(): iterable { return User::query()->with('roles')->orderBy('fullname')->get(); }
    public function find(int $id): ?User { return User::query()->with('roles')->find($id); }
    public function create(array $data): User { return User::query()->create($data); }
    public function update(User $user, array $data): User { $user->update($data); return $user->refresh(); }
}
