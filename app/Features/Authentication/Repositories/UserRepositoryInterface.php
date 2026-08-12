<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Repositories;

use Flex\Features\Authentication\Models\User;

interface UserRepositoryInterface
{
    public function all(): iterable;
    public function find(int $id): ?User;
    public function create(array $data): User;
    public function update(User $user, array $data): User;
}
