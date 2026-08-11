<?php

declare(strict_types=1);

namespace Flex\Core\Console\Commands;

use Flex\Core\Console\CommandInterface;
use Flex\Core\Features\Migrations\FeatureMigrationRunner;
use Illuminate\Database\Capsule\Manager as Capsule;
use InvalidArgumentException;

final class FeatureFreshCommand implements CommandInterface
{
    public function __construct(
        private readonly string $projectPath
    ) {
    }

    public function getName(): string
    {
        return 'feature:fresh';
    }

    public function getDescription(): string
    {
        return 'Връща всички миграции на feature и ги изпълнява отново.';
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
                . 'php flex feature:fresh Shopping'
            );
        }

        $runner = new FeatureMigrationRunner(
            connection: Capsule::connection(),
            projectPath: $this->projectPath
        );

        echo PHP_EOL;
        echo sprintf(
            'Fresh migrations за feature: %s%s',
            trim($feature),
            PHP_EOL
        );
        echo PHP_EOL;

        $result = $runner->fresh(
            featureName: $feature
        );

        echo 'Връщане на миграциите...' . PHP_EOL;

        if ($result['rolled_back'] === []) {
            echo '  Няма изпълнени миграции за връщане.' . PHP_EOL;
        } else {
            foreach ($result['rolled_back'] as $migration) {
                echo sprintf(
                    "  [ROLLED BACK] %s%s",
                    $migration,
                    PHP_EOL
                );
            }
        }

        echo PHP_EOL;
        echo 'Изпълнение на миграциите...' . PHP_EOL;

        if ($result['migrated'] === []) {
            echo '  Няма миграции за изпълнение.' . PHP_EOL;
        } else {
            foreach ($result['migrated'] as $migration) {
                echo sprintf(
                    "  [MIGRATED] %s%s",
                    $migration,
                    PHP_EOL
                );
            }
        }

        echo PHP_EOL;
        echo sprintf(
            'Върнати миграции: %d%s',
            count($result['rolled_back']),
            PHP_EOL
        );
        echo sprintf(
            'Изпълнени миграции: %d%s',
            count($result['migrated']),
            PHP_EOL
        );

        return 0;
    }
}
