<?php

declare(strict_types=1);

namespace Flex\Core\Features\Migrations;

use Flex\Core\Features\Migrations\Exceptions\FeatureNotFoundException;
use Flex\Core\Features\Migrations\Exceptions\MigrationAlreadyExistsException;
use Flex\Core\Features\Migrations\Support\MigrationName;
use InvalidArgumentException;
use RuntimeException;

final class FeatureMigrationGenerator
{
    public function __construct(
        private readonly string $projectPath
    ) {
    }

    public function generate(
        string $feature,
        string $migrationName
    ): string {
        $featureName = $this->normalizeFeatureName(
            $feature
        );

        $featurePath = $this->resolveFeaturePath(
            $featureName
        );

        $migrationsPath = $featurePath
            . DIRECTORY_SEPARATOR
            . 'Migrations';

        $this->ensureMigrationsDirectory(
            $migrationsPath
        );

        $normalizedName = MigrationName::normalize(
            $migrationName
        );

        $className = MigrationName::className(
            $migrationName
        );

        $this->assertMigrationDoesNotExist(
            $migrationsPath,
            $normalizedName
        );

        $filename = sprintf(
            '%s_%s.php',
            date('YmdHis'),
            $normalizedName
        );

        $targetPath = $migrationsPath
            . DIRECTORY_SEPARATOR
            . $filename;

        $contents = $this->renderStub(
            className: $className
        );

        if (file_put_contents($targetPath, $contents) === false) {
            throw new RuntimeException(
                sprintf(
                    'Неуспешно създаване на migration файла: %s',
                    $targetPath
                )
            );
        }

        return $targetPath;
    }

    private function normalizeFeatureName(string $feature): string
    {
        $feature = trim($feature);

        if ($feature === '') {
            throw new InvalidArgumentException(
                'Името на feature не може да бъде празно.'
            );
        }

        if (
            str_contains($feature, '/')
            || str_contains($feature, '\\')
            || str_contains($feature, '..')
            || !preg_match('/^[a-z0-9_-]+$/i', $feature)
        ) {
            throw new InvalidArgumentException(
                'Името на feature е невалидно.'
            );
        }

        return $feature;
    }

    private function resolveFeaturePath(string $feature): string
    {
        $featuresRoot = $this->projectPath
            . DIRECTORY_SEPARATOR
            . 'app'
            . DIRECTORY_SEPARATOR
            . 'Features';

        if (!is_dir($featuresRoot)) {
            throw new FeatureNotFoundException(
                sprintf(
                    'Features директорията не беше намерена в: %s',
                    $featuresRoot
                )
            );
        }

        $directPath = $featuresRoot
            . DIRECTORY_SEPARATOR
            . $feature;

        if (is_dir($directPath)) {
            return $directPath;
        }

        $entries = scandir($featuresRoot);

        if ($entries !== false) {
            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }

                $candidate = $featuresRoot
                    . DIRECTORY_SEPARATOR
                    . $entry;

                if (
                    is_dir($candidate)
                    && strcasecmp($entry, $feature) === 0
                ) {
                    return $candidate;
                }
            }
        }

        throw new FeatureNotFoundException(
            sprintf(
                'Feature "%s" не беше намерен в: %s',
                $feature,
                $featuresRoot
            )
        );
    }

    private function ensureMigrationsDirectory(string $path): void
    {
        if (is_dir($path)) {
            return;
        }

        if (!mkdir($path, 0775, true) && !is_dir($path)) {
            throw new RuntimeException(
                sprintf(
                    'Неуспешно създаване на директорията: %s',
                    $path
                )
            );
        }
    }

    private function assertMigrationDoesNotExist(
        string $path,
        string $name
    ): void {
        $matches = glob(
            $path
            . DIRECTORY_SEPARATOR
            . '*_'
            . $name
            . '.php'
        );

        if ($matches !== false && $matches !== []) {
            throw new MigrationAlreadyExistsException(
                sprintf(
                    'Migration "%s" вече съществува за този feature.',
                    $name
                )
            );
        }
    }

    private function renderStub(string $className): string
    {
        $stubPath = $this->projectPath
            . DIRECTORY_SEPARATOR
            . 'app'
            . DIRECTORY_SEPARATOR
            . 'Core'
            . DIRECTORY_SEPARATOR
            . 'Console'
            . DIRECTORY_SEPARATOR
            . 'Stubs'
            . DIRECTORY_SEPARATOR
            . 'feature-migration.stub';

        if (!is_file($stubPath)) {
            throw new RuntimeException(
                sprintf(
                    'Migration stub файлът не беше намерен в: %s',
                    $stubPath
                )
            );
        }

        $stub = file_get_contents($stubPath);

        if ($stub === false) {
            throw new RuntimeException(
                'Migration stub файлът не може да бъде прочетен.'
            );
        }

        return str_replace(
            '{{ class }}',
            $className,
            $stub
        );
    }
}
