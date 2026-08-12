<?php

declare(strict_types=1);

namespace Flex\Core\Console\Commands;

use Flex\Core\Console\CommandInterface;
use Flex\Core\Features\Migrations\FeatureMigrationRunner;
use Illuminate\Database\Capsule\Manager as Capsule;
use InvalidArgumentException;

final class FeatureStatusCommand implements CommandInterface
{
    public function __construct(
        private readonly string $projectPath
    ) {
    }

    public function getName(): string
    {
        return 'feature:status';
    }

    public function getDescription(): string
    {
        return 'Показва статуса на миграциите на feature.';
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
                . 'php flex feature:status Shopping'
            );
        }

        $runner = new FeatureMigrationRunner(
            connection: Capsule::connection(),
            projectPath: $this->projectPath
        );

        $migrations = $runner->status(
            featureName: $feature
        );

        echo PHP_EOL;
        echo sprintf(
            'Статус на миграциите за feature: %s%s',
            trim($feature),
            PHP_EOL
        );
        echo str_repeat('=', 94);
        echo PHP_EOL;
        echo PHP_EOL;

        if ($migrations === []) {
            echo 'Няма намерени migration файлове.' . PHP_EOL;

            return 0;
        }

        echo sprintf(
            "  %-12s %-10s %-48s %s%s",
            'Статус',
            'Batch',
            'Migration',
            'Изпълнена на',
            PHP_EOL
        );

        echo '  ' . str_repeat('-', 90);
        echo PHP_EOL;

        foreach ($migrations as $migration) {
            echo sprintf(
                "  %-12s %-10s %-48s %s%s",
                $migration['executed']
                    ? '[YES]'
                    : '[NO]',
                $migration['batch'] ?? '-',
                $migration['migration'],
                $migration['executed_at'] ?? '-',
                PHP_EOL
            );
        }

        $executedCount = count(
            array_filter(
                $migrations,
                static fn (array $migration): bool =>
                    $migration['executed']
            )
        );

        $pendingCount = count($migrations)
            - $executedCount;

        echo PHP_EOL;
        echo sprintf(
            'Общо: %d | Изпълнени: %d | Предстоящи: %d%s',
            count($migrations),
            $executedCount,
            $pendingCount,
            PHP_EOL
        );

        return 0;
    }
}
