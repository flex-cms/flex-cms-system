<?php

declare(strict_types=1);

namespace Flex\Core\Console\Commands;

use Flex\Core\Console\CommandInterface;
use Flex\Core\Features\Migrations\FeatureMigrationGenerator;
use InvalidArgumentException;

final class FeatureMakeMigrationCommand implements CommandInterface
{
    public function __construct(
        private readonly string $projectPath
    ) {
    }

    public function getName(): string
    {
        return 'feature:make-migration';
    }

    public function getDescription(): string
    {
        return 'Създава нов migration файл за feature.';
    }

    /**
     * @param array<int, string> $arguments
     */
    public function handle(array $arguments): int
    {
        $featureArgument = $arguments[0] ?? null;
        $migrationArgument = $arguments[1] ?? null;

        if (
            !is_string($featureArgument)
            || trim($featureArgument) === ''
        ) {
            throw new InvalidArgumentException(
                'Посочете име на feature. Например: '
                . 'php flex feature:make-migration Shopping create_products_table'
            );
        }

        if (
            !is_string($migrationArgument)
            || trim($migrationArgument) === ''
        ) {
            throw new InvalidArgumentException(
                'Посочете име на migration. Например: '
                . 'php flex feature:make-migration Shopping create_products_table'
            );
        }

        $generator = new FeatureMigrationGenerator(
            projectPath: $this->projectPath
        );

        $path = $generator->generate(
            feature: $featureArgument,
            migrationName: $migrationArgument
        );

        echo PHP_EOL;
        echo sprintf(
            'Migration файлът е създаден успешно.%s',
            PHP_EOL
        );
        echo sprintf(
            'Feature: %s%s',
            trim($featureArgument),
            PHP_EOL
        );
        echo sprintf(
            'Файл: %s%s',
            $path,
            PHP_EOL
        );
        echo PHP_EOL;

        return 0;
    }
}
