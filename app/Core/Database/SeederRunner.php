<?php

declare(strict_types=1);

namespace Flex\Core\Database;

use InvalidArgumentException;
use Throwable;

final class SeederRunner
{
    public function __construct(
        private readonly SeederDiscovery $discovery,
    ) {
    }

    /**
     * Изпълнява seeders на всички Features.
     *
     * @return array<string>
     */
    public function runAll(): array
    {
        $seeders = $this->discovery->discover();

        $executed = [];

        foreach ($seeders as $seederClass) {
            $this->runSeeder($seederClass);

            $executed[] = $seederClass;
        }

        return $executed;
    }

    /**
     * Изпълнява root seeder-а на конкретен Feature.
     */
    public function runFeature(string $featureName): string
    {
        $seederClass = $this->discovery->discoverFeature(
            $featureName
        );

        if ($seederClass === null) {
            throw new InvalidArgumentException(
                sprintf(
                    'Feature [%s] does not have a root seeder.',
                    $featureName,
                )
            );
        }

        $this->runSeeder($seederClass);

        return $seederClass;
    }

    /**
     * @param class-string<Seeder> $seederClass
     */
    public function runSeeder(string $seederClass): void
    {
        if (!class_exists($seederClass)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Seeder [%s] does not exist.',
                    $seederClass,
                )
            );
        }

        $seeder = new $seederClass();

        if (!$seeder instanceof Seeder) {
            throw new InvalidArgumentException(
                sprintf(
                    'Seeder [%s] must extend [%s].',
                    $seederClass,
                    Seeder::class,
                )
            );
        }

        try {
            $seeder->run();
        } catch (Throwable $exception) {
            throw new SeederException(
                sprintf(
                    'Seeder [%s] failed: %s',
                    $seederClass,
                    $exception->getMessage(),
                ),
                previous: $exception,
            );
        }
    }
}
