<?php

declare(strict_types=1);

namespace Flex\Features\Dashboard\Data;

use Illuminate\Support\Collection;

final readonly class DashboardData
{
    public function __construct(
        public array $stats,
        public Collection $recentPages,
        public Collection $recentLogins,
        public array $system,
    ) {}

    public function toArray(): array
    {
        return [
            'title' => 'Табло за управление',
            'stats' => $this->stats,
            'recentPages' => $this->recentPages,
            'recentLogins' => $this->recentLogins,
            'system' => $this->system,
        ];
    }
}
