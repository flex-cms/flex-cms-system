<?php

declare(strict_types=1);

namespace Flex\Core\Console\Commands;

use Flex\Core\Console\CommandInterface;
use Flex\Core\Plugins\Migrations\PluginMigrationRunner;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Connection;
use InvalidArgumentException;
use RuntimeException;

final class PluginStatusCommand implements CommandInterface
{
    public function __construct(
        private readonly string $projectPath
    ) {
    }

    public function getName(): string
    {
        return 'plugin:status';
    }

    public function getDescription(): string
    {
        return 'Показва статуса на миграциите на плъгин.';
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
                'Посочете име на плъгин. Например: php flex plugin:status shopping'
            );
        }

        $pluginName = $this->normalizePluginName(
            $pluginArgument
        );

        $pluginPath = $this->resolvePluginPath(
            $pluginName
        );

        $runner = new PluginMigrationRunner(
            $this->resolveConnection()
        );

        $migrations = $runner->status(
            pluginName: $pluginName,
            pluginPath: $pluginPath
        );

        echo PHP_EOL;

        echo sprintf(
            'Статус на миграциите за плъгин: %s%s',
            $pluginName,
            PHP_EOL
        );

        echo str_repeat('=', 90);
        echo PHP_EOL;
        echo PHP_EOL;

        if ($migrations === []) {
            echo 'Няма намерени миграционни файлове.';
            echo PHP_EOL;

            return 0;
        }

        echo sprintf(
            "  %-12s %-10s %-42s %s%s",
            'Статус',
            'Batch',
            'Миграция',
            'Изпълнена на',
            PHP_EOL
        );

        echo '  ' . str_repeat('-', 86);
        echo PHP_EOL;

        foreach ($migrations as $migration) {
            echo sprintf(
                "  %-12s %-10s %-42s %s%s",
                $migration['executed']
                    ? '[YES]'
                    : '[NO]',
                $migration['batch'] ?? '-',
                $migration['migration'],
                $migration['executed_at'] ?? '-',
                PHP_EOL
            );
        }

        echo PHP_EOL;

        $executedCount = count(
            array_filter(
                $migrations,
                static fn (array $migration): bool =>
                    $migration['executed']
            )
        );

        $pendingCount = count($migrations)
            - $executedCount;

        echo sprintf(
            'Общо: %d | Изпълнени: %d | Предстоящи: %d%s',
            count($migrations),
            $executedCount,
            $pendingCount,
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

    private function resolveConnection(): Connection
    {
        return Capsule::connection();
    }
}