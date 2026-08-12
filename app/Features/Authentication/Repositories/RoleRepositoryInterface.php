<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Repositories;

use Flex\Features\Authentication\Models\Role;

interface RoleRepositoryInterface
{
    public function all(): iterable;
    public function find(int $id): ?Role;
}
