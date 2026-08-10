<?php

declare(strict_types=1);

namespace Flex\Features\Dashboard\Services;

use Flex\Features\Dashboard\Data\DashboardData;
use Flex\Features\Dashboard\Repositories\DashboardRepositoryInterface;

final readonly class DashboardService
{
    public function __construct(private DashboardRepositoryInterface $repository) {}

    public function data(): DashboardData
    {
        return new DashboardData(
            stats: $this->repository->statistics(),
            recentPages: $this->repository->recentPages(),
            recentLogins: $this->repository->recentLogins(),
            system: $this->repository->systemInformation(),
        );
    }
}
