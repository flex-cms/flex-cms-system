<?php

declare(strict_types=1);

namespace Flex\Core\Plugins\Migrations;

use Flex\Core\Plugins\Migrations\Contracts\PluginMigrationInterface;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use RuntimeException;
use Throwable;

final class PluginMigrationRunner
{
    private const MIGRATIONS_TABLE = 'plugin_migrations';

    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function migrate(
        string $pluginName,
        string $pluginPath,
        string $tablePrefix
    ): array {
        $this->ensureMigrationsTableExists();

        $migrationDirectory = $this->resolveMigrationDirectory(
            $pluginPath
        );

        $files = $this->getMigrationFiles(
            $migrationDirectory
        );

        $pendingFiles = array_filter(
            $files,
            function (string $file) use ($pluginName): bool {
                $migrationName = $this->getMigrationName(
                    $file
                );

                return !$this->hasRun(
                    pluginName: $pluginName,
                    migrationName: $migrationName
                );
            }
        );

        if ($pendingFiles === []) {
            return [];
        }

        $batch = $this->getNextBatch($pluginName);
        $executed = [];

        foreach ($pendingFiles as $file) {
            $migrationName = $this->getMigrationName(
                $file
            );

            $migration = $this->loadMigration($file);

            $migration->configure(
                connection: $this->connection,
                tablePrefix: $tablePrefix
            );

            $this->executeUp(
                migration: $migration,
                pluginName: $pluginName,
                migrationName: $migrationName,
                batch: $batch
            );

            $executed[] = $migrationName;
        }

        return $executed;
    }

    private function ensureMigrationsTableExists(): void
    {
        $schema = $this->connection
            ->getSchemaBuilder();

        if ($schema->hasTable(self::MIGRATIONS_TABLE)) {
            return;
        }

        $schema->create(
            self::MIGRATIONS_TABLE,
            function (Blueprint $table): void {
                $table->bigIncrements('id');

                $table->string('plugin', 150);
                $table->string('migration', 255);

                $table->unsignedInteger('batch')
                    ->default(1);

                $table->timestamp('executed_at')
                    ->nullable();

                $table->unique(
                    ['plugin', 'migration'],
                    'plugin_migrations_unique'
                );

                $table->index(
                    ['plugin', 'batch'],
                    'plugin_migrations_batch_index'
                );
            }
        );
    }

    private function resolveMigrationDirectory(
        string $pluginPath
    ): string {
        $directory = rtrim(
            $pluginPath,
            DIRECTORY_SEPARATOR
        )
            . DIRECTORY_SEPARATOR
            . 'Database'
            . DIRECTORY_SEPARATOR
            . 'Migrations';

        if (!is_dir($directory)) {
            throw new RuntimeException(
                sprintf(
                    'Папката с миграции не съществува: %s',
                    $directory
                )
            );
        }

        return $directory;
    }

    /**
     * @return array<int, string>
     */
    private function getMigrationFiles(
        string $directory
    ): array {
        $files = glob(
            $directory
            . DIRECTORY_SEPARATOR
            . '*.php'
        );

        if ($files === false) {
            throw new RuntimeException(
                sprintf(
                    'Миграциите не могат да бъдат прочетени: %s',
                    $directory
                )
            );
        }

        sort($files, SORT_STRING);

        return array_values($files);
    }

    private function loadMigration(
        string $file
    ): PluginMigrationInterface {
        /*
         * Използваме require, а не require_once.
         *
         * Всеки миграционен файл връща нов анонимен
         * обект при всяко зареждане.
         */
        $migration = require $file;

        if (!$migration instanceof PluginMigrationInterface) {
            throw new RuntimeException(
                sprintf(
                    'Миграционният файл "%s" трябва да върне обект, който имплементира %s.',
                    $file,
                    PluginMigrationInterface::class
                )
            );
        }

        return $migration;
    }

    private function executeUp(
        PluginMigrationInterface $migration,
        string $pluginName,
        string $migrationName,
        int $batch
    ): void {
        $callback = function () use ($migration, $pluginName, $migrationName, $batch): void {
            $migration->up();

            $this->recordMigration(
                pluginName: $pluginName,
                migrationName: $migrationName,
                batch: $batch
            );
        };

        if ($migration->shouldUseTransaction()) {
            $this->connection->transaction(
                $callback
            );

            return;
        }

        try {
            $callback();
        } catch (Throwable $exception) {
            throw new RuntimeException(
                sprintf(
                    'Неуспешна миграция "%s": %s',
                    $migrationName,
                    $exception->getMessage()
                ),
                previous: $exception
            );
        }
    }

    private function hasRun(
        string $pluginName,
        string $migrationName
    ): bool {
        return $this->connection
            ->table(self::MIGRATIONS_TABLE)
            ->where('plugin', '=', $pluginName)
            ->where('migration', '=', $migrationName)
            ->exists();
    }

    private function getNextBatch(
        string $pluginName
    ): int {
        $lastBatch = $this->connection
            ->table(self::MIGRATIONS_TABLE)
            ->where('plugin', '=', $pluginName)
            ->max('batch');

        return ((int) $lastBatch) + 1;
    }

    private function recordMigration(
        string $pluginName,
        string $migrationName,
        int $batch
    ): void {
        $this->connection
            ->table(self::MIGRATIONS_TABLE)
            ->insert([
                'plugin' => $pluginName,
                'migration' => $migrationName,
                'batch' => $batch,
                'executed_at' => date('Y-m-d H:i:s'),
            ]);
    }

    private function getMigrationName(
        string $file
    ): string {
        return pathinfo(
            $file,
            PATHINFO_FILENAME
        );
    }

    public function rollback(
        string $pluginName,
        string $pluginPath,
        string $tablePrefix
    ): array {
        $this->ensureMigrationsTableExists();

        $lastBatch = $this->connection
            ->table(self::MIGRATIONS_TABLE)
            ->where('plugin', '=', $pluginName)
            ->max('batch');

        if ($lastBatch === null) {
            return [];
        }

        $migrationRecords = $this->connection
            ->table(self::MIGRATIONS_TABLE)
            ->where('plugin', '=', $pluginName)
            ->where('batch', '=', (int) $lastBatch)
            ->orderByDesc('id')
            ->get();

        if ($migrationRecords->isEmpty()) {
            return [];
        }

        $migrationDirectory = $this->resolveMigrationDirectory(
            $pluginPath
        );

        $rolledBack = [];

        foreach ($migrationRecords as $record) {
            $migrationName = (string) $record->migration;

            $file = $migrationDirectory
                . DIRECTORY_SEPARATOR
                . $migrationName
                . '.php';

            if (!is_file($file)) {
                throw new RuntimeException(
                    sprintf(
                        'Файлът на миграцията "%s" не беше намерен.',
                        $file
                    )
                );
            }

            $migration = $this->loadMigration($file);

            $migration->configure(
                connection: $this->connection,
                tablePrefix: $tablePrefix
            );

            $this->executeDown(
                migration: $migration,
                pluginName: $pluginName,
                migrationName: $migrationName
            );

            $rolledBack[] = $migrationName;
        }

        return $rolledBack;
    }

    private function executeDown(
        PluginMigrationInterface $migration,
        string $pluginName,
        string $migrationName
    ): void {
        $callback = function () use ($migration, $pluginName, $migrationName): void {
            $migration->down();

            $this->connection
                ->table(self::MIGRATIONS_TABLE)
                ->where('plugin', '=', $pluginName)
                ->where('migration', '=', $migrationName)
                ->delete();
        };

        try {
            if ($migration->shouldUseTransaction()) {
                $this->connection->transaction($callback);

                return;
            }

            $callback();
        } catch (Throwable $exception) {
            throw new RuntimeException(
                sprintf(
                    'Неуспешен rollback на миграцията "%s": %s',
                    $migrationName,
                    $exception->getMessage()
                ),
                previous: $exception
            );
        }
    }

    /**
     * Връща статуса на всички миграции на плъгина.
     *
     * @return array<int, array{
     *     migration: string,
     *     executed: bool,
     *     batch: int|null,
     *     executed_at: string|null
     * }>
     */
    public function status(
        string $pluginName,
        string $pluginPath
    ): array {
        $this->ensureMigrationsTableExists();

        $migrationDirectory = $this->resolveMigrationDirectory(
            $pluginPath
        );

        $files = $this->getMigrationFiles(
            $migrationDirectory
        );

        $executedMigrations = $this->connection
            ->table(self::MIGRATIONS_TABLE)
            ->where('plugin', '=', $pluginName)
            ->get()
            ->keyBy('migration');

        $status = [];

        foreach ($files as $file) {
            $migrationName = $this->getMigrationName(
                $file
            );

            $record = $executedMigrations->get(
                $migrationName
            );

            $status[] = [
                'migration' => $migrationName,
                'executed' => $record !== null,
                'batch' => $record !== null
                    ? (int) $record->batch
                    : null,
                'executed_at' => $record !== null
                    ? $record->executed_at
                    : null,
            ];
        }

        return $status;
    }

    /**
     * Връща всички изпълнени миграции на конкретен плъгин.
     *
     * @return array<int, string>
     */
    public function rollbackAll(
        string $pluginName,
        string $pluginPath,
        string $tablePrefix
    ): array {
        $this->ensureMigrationsTableExists();

        $migrationRecords = $this->connection
            ->table(self::MIGRATIONS_TABLE)
            ->where('plugin', '=', $pluginName)
            ->orderByDesc('batch')
            ->orderByDesc('id')
            ->get();

        if ($migrationRecords->isEmpty()) {
            return [];
        }

        $migrationDirectory = $this->resolveMigrationDirectory(
            $pluginPath
        );

        $rolledBack = [];

        foreach ($migrationRecords as $record) {
            $migrationName = (string) $record->migration;

            $file = $migrationDirectory
                . DIRECTORY_SEPARATOR
                . $migrationName
                . '.php';

            if (!is_file($file)) {
                throw new RuntimeException(
                    sprintf(
                        'Файлът на миграцията "%s" не беше намерен.',
                        $file
                    )
                );
            }

            $migration = $this->loadMigration($file);

            $migration->configure(
                connection: $this->connection,
                tablePrefix: $tablePrefix
            );

            $this->executeDown(
                migration: $migration,
                pluginName: $pluginName,
                migrationName: $migrationName
            );

            $rolledBack[] = $migrationName;
        }

        return $rolledBack;
    }

    /**
     * Връща всички миграции на плъгина и ги изпълнява отново.
     *
     * @return array{
     *     rolled_back: array<int, string>,
     *     migrated: array<int, string>
     * }
     */
    public function fresh(
        string $pluginName,
        string $pluginPath,
        string $tablePrefix
    ): array {
        $rolledBack = $this->rollbackAll(
            pluginName: $pluginName,
            pluginPath: $pluginPath,
            tablePrefix: $tablePrefix
        );

        $migrated = $this->migrate(
            pluginName: $pluginName,
            pluginPath: $pluginPath,
            tablePrefix: $tablePrefix
        );

        return [
            'rolled_back' => $rolledBack,
            'migrated' => $migrated,
        ];
    }
}
