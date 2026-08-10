<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Dashboard\Services;

use Flex\Features\Dashboard\Repositories\DashboardRepositoryInterface;
use Flex\Features\Dashboard\Services\DashboardService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

final class DashboardServiceTest extends TestCase
{
    public function testItBuildsDashboardData(): void
    {
        $repository = $this->createMock(DashboardRepositoryInterface::class);
        $pages = new Collection([(object) ['id' => 1]]);
        $logins = new Collection([(object) ['id' => 2]]);
        $stats = ['users_count' => 10];
        $system = ['version' => '1.0.0'];

        $repository->expects(self::once())->method('statistics')->willReturn($stats);
        $repository->expects(self::once())->method('recentPages')->willReturn($pages);
        $repository->expects(self::once())->method('recentLogins')->willReturn($logins);
        $repository->expects(self::once())->method('systemInformation')->willReturn($system);

        $data = (new DashboardService($repository))->data();

        self::assertSame($stats, $data->stats);
        self::assertSame($pages, $data->recentPages);
        self::assertSame($logins, $data->recentLogins);
        self::assertSame($system, $data->system);
        self::assertSame('Табло за управление', $data->toArray()['title']);
    }
}
