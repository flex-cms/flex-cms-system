<?php

declare(strict_types=1);

namespace Flex\Core\Console\Commands;

use Flex\Core\Console\CommandInterface;
use Flex\Core\Features\Migrations\FeatureMigrationRunner;
use Illuminate\Database\Capsule\Manager as Capsule;
use InvalidArgumentException;

final class FeatureMigrateCommand implements CommandInterface
{
    public function __construct(
        private readonly string $projectPath
    ) {
    }

    public function getName(): string
    {
        return 'feature:migrate';
    }

    public function getDescription(): string
    {
        return 'Изпълнява неизпълнените миграции на feature или на всички features.';
    }

    /**
     * @param array<int, string> $arguments
     */
    public function handle(array $arguments): int
    {
        $argument = $arguments[0] ?? null;

        if (!is_string($argument) || trim($argument) === '') {
            throw new InvalidArgumentException(
                'Посочете име на feature или --all. Например: '
                . 'php flex feature:migrate Shopping'
            );
        }

        $runner = new FeatureMigrationRunner(
            connection: Capsule::connection(),
            projectPath: $this->projectPath
        );

        if ($argument === '--all') {
            return $this->migrateAll(
                $runner
            );
        }

        return $this->migrateFeature(
            $runner,
            $argument
        );
    }

    private function migrateFeature(
        FeatureMigrationRunner $runner,
        string $feature
    ): int {
        echo PHP_EOL;
        echo sprintf(
            'Мигриране на feature: %s%s',
            trim($feature),
            PHP_EOL
        );
        echo PHP_EOL;

        $executed = $runner->migrate(
            featureName: $feature
        );

        if ($executed === []) {
            echo 'Няма нови миграции.' . PHP_EOL;

            return 0;
        }

        foreach ($executed as $migration) {
            echo sprintf(
                "  [OK] %s%s",
                $migration,
                PHP_EOL
            );
        }

        echo PHP_EOL;
        echo sprintf(
            'Успешно изпълнени миграции: %d%s',
            count($executed),
            PHP_EOL
        );

        return 0;
    }

    private function migrateAll(
        FeatureMigrationRunner $runner
    ): int {
        $features = $runner
            ->discoverFeaturesWithMigrations();

        echo PHP_EOL;
        echo 'Мигриране на всички features' . PHP_EOL;
        echo str_repeat('=', 40) . PHP_EOL;
        echo PHP_EOL;

        if ($features === []) {
            echo 'Няма features с migration файлове.' . PHP_EOL;

            return 0;
        }

        $totalExecuted = 0;
        $featuresWithChanges = 0;

        foreach ($features as $feature) {
            echo sprintf(
                '[%s]%s',
                $feature,
                PHP_EOL
            );

            $executed = $runner->migrate(
                featureName: $feature
            );

            if ($executed === []) {
                echo '  Няма нови миграции.' . PHP_EOL;
                echo PHP_EOL;

                continue;
            }

            $featuresWithChanges++;

            foreach ($executed as $migration) {
                echo sprintf(
                    "  [OK] %s%s",
                    $migration,
                    PHP_EOL
                );
            }

            $totalExecuted += count(
                $executed
            );

            echo PHP_EOL;
        }

        echo str_repeat('-', 40) . PHP_EOL;
        echo sprintf(
            'Features с промени: %d%s',
            $featuresWithChanges,
            PHP_EOL
        );
        echo sprintf(
            'Общо изпълнени миграции: %d%s',
            $totalExecuted,
            PHP_EOL
        );

        return 0;
    }
}
