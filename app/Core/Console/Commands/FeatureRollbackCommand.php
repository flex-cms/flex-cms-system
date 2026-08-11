<?php

declare(strict_types=1);

namespace Flex\Core\Console\Commands;

use Flex\Core\Console\CommandInterface;
use Flex\Core\Features\Migrations\FeatureMigrationRunner;
use Illuminate\Database\Capsule\Manager as Capsule;
use InvalidArgumentException;

final class FeatureRollbackCommand implements CommandInterface
{
    public function __construct(
        private readonly string $projectPath
    ) {
    }

    public function getName(): string
    {
        return 'feature:rollback';
    }

    public function getDescription(): string
    {
        return 'Връща последния batch миграции на feature.';
    }

    /**
     * @param array<int, string> $arguments
     */
    public function handle(array $arguments): int
    {
        $feature = $arguments[0] ?? null;

        if (!is_string($feature) || trim($feature) === '') {
            throw new InvalidArgumentException(
                'Посочете име на feature. Например: '
                . 'php flex feature:rollback Shopping'
            );
        }

        $runner = new FeatureMigrationRunner(
            connection: Capsule::connection(),
            projectPath: $this->projectPath
        );

        echo PHP_EOL;
        echo sprintf(
            'Rollback на feature: %s%s',
            trim($feature),
            PHP_EOL
        );
        echo PHP_EOL;

        $rolledBack = $runner->rollback(
            featureName: $feature
        );

        if ($rolledBack === []) {
            echo 'Няма migration-и за връщане.' . PHP_EOL;

            return 0;
        }

        foreach ($rolledBack as $migration) {
            echo sprintf(
                "  [ROLLED BACK] %s%s",
                $migration,
                PHP_EOL
            );
        }

        echo PHP_EOL;
        echo sprintf(
            'Успешно върнати миграции: %d%s',
            count($rolledBack),
            PHP_EOL
        );

        return 0;
    }
}
