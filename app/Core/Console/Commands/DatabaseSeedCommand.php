<?php

declare(strict_types=1);

namespace Flex\Core\Console\Commands;

use Flex\Core\Console\CommandInterface;
use Flex\Core\Database\SeederDiscovery;
use Flex\Core\Database\SeederRunner;

final class DatabaseSeedCommand implements CommandInterface
{
    public function __construct(
        private readonly string $projectPath,
    ) {
    }

    public function getName(): string
    {
        return 'db:seed';
    }

    public function getDescription(): string
    {
        return 'Изпълнява seeders за всички или за конкретен Feature.';
    }

    /**
     * @param array<int, string> $arguments
     */
    public function handle(array $arguments): int
    {
        $feature = $arguments[0] ?? null;

        $discovery = new SeederDiscovery(
            featuresPath: $this->projectPath . '/app/Features',
        );

        $runner = new SeederRunner($discovery);

        if ($feature !== null) {
            return $this->seedFeature(
                $runner,
                $feature,
            );
        }

        return $this->seedAll($runner);
    }

    private function seedAll(
        SeederRunner $runner,
    ): int {
        echo PHP_EOL;
        echo 'Seeding database...';
        echo PHP_EOL;
        echo PHP_EOL;

        $executed = $runner->runAll();

        if ($executed === []) {
            echo 'Не бяха намерени seeders.';
            echo PHP_EOL;

            return 0;
        }

        foreach ($executed as $seederClass) {
            echo sprintf(
                '  %-40s DONE%s',
                $this->shortName($seederClass),
                PHP_EOL,
            );
        }

        echo PHP_EOL;
        echo 'Database seeded successfully.';
        echo PHP_EOL;

        return 0;
    }

    private function seedFeature(
        SeederRunner $runner,
        string $feature,
    ): int {
        echo PHP_EOL;

        echo sprintf(
            'Seeding feature [%s]...',
            $feature,
        );

        echo PHP_EOL;
        echo PHP_EOL;

        $seederClass = $runner->runFeature(
            $feature,
        );

        echo sprintf(
            '  %-40s DONE%s',
            $this->shortName($seederClass),
            PHP_EOL,
        );

        echo PHP_EOL;

        echo sprintf(
            'Feature [%s] seeded successfully.',
            $feature,
        );

        echo PHP_EOL;

        return 0;
    }

    private function shortName(
        string $class,
    ): string {
        $position = strrpos($class, '\\');

        if ($position === false) {
            return $class;
        }

        return substr(
            $class,
            $position + 1,
        );
    }
}
