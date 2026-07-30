<?php

declare(strict_types=1);

namespace Flex\Core\Console\Commands;

use Flex\Core\Console\CommandInterface;
use Flex\Core\Plugins\Migrations\PluginMigrationRunner;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Connection;
use InvalidArgumentException;
use RuntimeException;

final class PluginRollbackCommand implements CommandInterface
{
    public function __construct(
        private readonly string $projectPath
    ) {
    }

    public function getName(): string
    {
        return 'plugin:rollback';
    }

    public function getDescription(): string
    {
        return 'Връща последния batch миграции на плъгин.';
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
                'Посочете име на плъгин. Например: php flex plugin:rollback shopping'
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

        echo sprintf(
            'Rollback на плъгин: %s%s',
            $pluginName,
            PHP_EOL
        );

        echo sprintf(
            'Префикс на таблиците: %s%s',
            $tablePrefix,
            PHP_EOL
        );

        echo PHP_EOL;

        $rolledBack = $runner->rollback(
            pluginName: $pluginName,
            pluginPath: $pluginPath,
            tablePrefix: $tablePrefix
        );

        if ($rolledBack === []) {
            echo 'Няма миграции за връщане.';
            echo PHP_EOL;

            return 0;
        }

        foreach ($rolledBack as $migrationName) {
            echo sprintf(
                "  [ROLLED BACK] %s%s",
                $migrationName,
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

    private function normalizePluginName(
        string $pluginName
    ): string {
        $pluginName = trim($pluginName);

        $pluginName = preg_replace(
            '/^flex-plugin-/i',
            '',
            $pluginName
        );

        if (
            !is_string($pluginName)
            || $pluginName === ''
        ) {
            throw new InvalidArgumentException(
                'Невалидно име на плъгин.'
            );
        }

        if (!preg_match('/^[a-z0-9_-]+$/i', $pluginName)) {
            throw new InvalidArgumentException(
                'Името на плъгина може да съдържа само букви, цифри, тире и долна черта.'
            );
        }

        return strtolower($pluginName);
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