<?php

declare(strict_types=1);

namespace Flex\Core\Console\Commands;

use Flex\Core\Console\CommandInterface;
use Flex\Core\Plugins\Migrations\PluginMigrationRunner;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Connection;
use InvalidArgumentException;
use RuntimeException;

final class PluginFreshCommand implements CommandInterface
{
    public function __construct(
        private readonly string $projectPath
    ) {
    }

    public function getName(): string
    {
        return 'plugin:fresh';
    }

    public function getDescription(): string
    {
        return 'Връща всички миграции на плъгин и ги изпълнява отново.';
    }

    /**
     * @param array<int, string> $arguments
     */
    public function handle(array $arguments): int
    {
        $pluginArgument = $arguments[0] ?? null;

        if (
            !is_string($pluginArgument)
            || trim($pluginArgument) === ''
        ) {
            throw new InvalidArgumentException(
                'Посочете име на плъгин. Например: '
                . 'php flex plugin:fresh shopping'
            );
        }

        $pluginName = $this->normalizePluginName(
            $pluginArgument
        );

        $pluginPath = $this->resolvePluginPath(
            $pluginName
        );

        $tablePrefix = $this->resolveTablePrefix(
            $pluginName
        );

        $runner = new PluginMigrationRunner(
            $this->resolveConnection()
        );

        echo PHP_EOL;

        echo sprintf(
            'Обновяване на миграциите за плъгин: %s%s',
            $pluginName,
            PHP_EOL
        );

        echo sprintf(
            'Префикс на таблиците: %s%s',
            $tablePrefix,
            PHP_EOL
        );

        echo PHP_EOL;

        echo 'Връщане на изпълнените миграции...';
        echo PHP_EOL;

        $result = $runner->fresh(
            pluginName: $pluginName,
            pluginPath: $pluginPath,
            tablePrefix: $tablePrefix
        );

        if ($result['rolled_back'] === []) {
            echo '  Няма изпълнени миграции за връщане.';
            echo PHP_EOL;
        } else {
            foreach ($result['rolled_back'] as $migrationName) {
                echo sprintf(
                    "  [ROLLED BACK] %s%s",
                    $migrationName,
                    PHP_EOL
                );
            }
        }

        echo PHP_EOL;
        echo 'Изпълнение на миграциите...';
        echo PHP_EOL;

        if ($result['migrated'] === []) {
            echo '  Няма миграции за изпълнение.';
            echo PHP_EOL;
        } else {
            foreach ($result['migrated'] as $migrationName) {
                echo sprintf(
                    "  [MIGRATED] %s%s",
                    $migrationName,
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

    private function normalizePluginName(
        string $pluginName
    ): string {
        $pluginName = trim($pluginName);

        $normalizedName = preg_replace(
            '/^flex-plugin-/i',
            '',
            $pluginName
        );

        if (
            !is_string($normalizedName)
            || $normalizedName === ''
        ) {
            throw new InvalidArgumentException(
                'Невалидно име на плъгин.'
            );
        }

        if (
            !preg_match(
                '/^[a-z0-9_-]+$/i',
                $normalizedName
            )
        ) {
            throw new InvalidArgumentException(
                'Името на плъгина може да съдържа само '
                . 'букви, цифри, тире и долна черта.'
            );
        }

        return strtolower($normalizedName);
    }

    private function resolvePluginPath(
        string $pluginName
    ): string {
        $path = $this->projectPath
            . DIRECTORY_SEPARATOR
            . 'plugins'
            . DIRECTORY_SEPARATOR
            . 'flex-plugin-' . $pluginName;

        if (!is_dir($path)) {
            throw new RuntimeException(
                sprintf(
                    'Плъгинът "%s" не беше намерен в: %s',
                    $pluginName,
                    $path
                )
            );
        }

        return $path;
    }

    private function resolveTablePrefix(
        string $pluginName
    ): string {
        return str_replace(
            '-',
            '_',
            $pluginName
        );
    }

    private function resolveConnection(): Connection
    {
        return Capsule::connection();
    }
}