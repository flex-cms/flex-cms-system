<?php

declare(strict_types=1);

namespace Flex\Core\Routing;

final readonly class FeatureRouteLoadResult
{
    /**
     * @param list<string> $loadedFeatures
     * @param list<string> $skippedFeatures
     * @param list<string> $loadedFiles
     */
    public function __construct(
        public array $loadedFeatures,
        public array $skippedFeatures,
        public array $loadedFiles,
    ) {
    }

    public function loadedCount(): int
    {
        return count($this->loadedFiles);
    }
}
