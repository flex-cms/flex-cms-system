<?php

declare(strict_types=1);

namespace Flex\Features\Dashboard\Repositories;

use Illuminate\Support\Collection;

interface DashboardRepositoryInterface
{
    public function statistics(): array;

    public function recentPages(int $limit = 5): Collection;

    public function recentLogins(int $limit = 5): Collection;

    public function systemInformation(): array;
}
