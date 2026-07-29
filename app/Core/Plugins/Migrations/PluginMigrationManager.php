<?php

namespace Flex\Core\Plugins\Migrations;

use Flex\Core\Plugins\Migrations\Contracts\PluginMigrationInterface;
use Illuminate\Database\Connection;
use RuntimeException;
use Throwable;

final class PluginMigrationManager
{
    private PluginMigrationRepository $repository;

    public function __construct(
        private readonly Connection $connection,
        ?PluginMigrationRepository $repository = null,
    ) {
        $this->repository = $repository ?? new PluginMigrationRepository($connection);
    }

    public function pending(string $pluginSlug, string $migrationsPath): array
    {
        $files = $this->discover($migrationsPath);
        $completed = $this->repository->completed($pluginSlug);
        $pending = [];

        foreach ($files as $migration => $path) {
            $checksum = hash_file('sha256', $path);

            if (isset($completed[$migration])) {
                if (!hash_equals($completed[$migration], $checksum)) {
                    throw new RuntimeException(
                        "Migration файлът '{$migration}' е променен след неговото изпълнение."
                    );
                }

                continue;
            }

            $pending[$migration] = $path;
        }

        return $pending;
    }

    public function migrate(
        string $pluginSlug,
        string $pluginVersion,
        string $migrationsPath,
        string $tablePrefix,
    ): PluginMigrationResult {
        $this->validatePluginSlug($pluginSlug);
        $this->acquireLock($pluginSlug);

        try {
            $pending = $this->pending($pluginSlug, $migrationsPath);
            $batch = $pending === [] ? 0 : $this->repository->nextBatch($pluginSlug);
            $executed = [];

            foreach ($pending as $migration => $path) {
                $checksum = hash_file('sha256', $path);
                $startedAt = hrtime(true);

                $this->repository->markRunning(
                    $pluginSlug,
                    $migration,
                    $pluginVersion,
                    $batch,
                    $checksum
                );

                try {
                    $instance = $this->loadMigration($path, $tablePrefix);

                    $operation = function () use (
                        $instance,
                        $pluginSlug,
                        $migration,
                        $startedAt
                    ): void {
                        $instance->up();

                        $elapsed = (int) round((hrtime(true) - $startedAt) / 1_000_000);
                        $this->repository->markCompleted($pluginSlug, $migration, $elapsed);
                    };

                    $instance->shouldUseTransaction()
                        ? $this->connection->transaction($operation)
                        : $operation();
                } catch (Throwable $exception) {
                    $this->repository->markFailed($pluginSlug, $migration, $exception->getMessage());
                    throw new RuntimeException(
                        "Неуспешна plugin migration '{$migration}': {$exception->getMessage()}",
                        0,
                        $exception
                    );
                }

                $executed[] = $migration;
            }

            return new PluginMigrationResult(
                pluginSlug: $pluginSlug,
                batch: $batch,
                migrations: $executed,
                message: $executed === []
                    ? 'Няма чакащи migrations.'
                    : 'Plugin migrations бяха изпълнени успешно.',
            );
        } finally {
            $this->releaseLock($pluginSlug);
        }
    }

    public function rollback(
        string $pluginSlug,
        string $migrationsPath,
        string $tablePrefix,
    ): PluginMigrationResult {
        $this->validatePluginSlug($pluginSlug);
        $this->acquireLock($pluginSlug);

        try {
            $batch = $this->repository->latestCompletedBatch($pluginSlug);

            if ($batch === 0) {
                return new PluginMigrationResult(
                    pluginSlug: $pluginSlug,
                    batch: 0,
                    migrations: [],
                    message: 'Няма migrations за връщане.',
                );
            }

            $files = $this->discover($migrationsPath);
            $rolledBack = [];

            foreach ($this->repository->completedInBatch($pluginSlug, $batch) as $record) {
                $path = $files[$record->migration] ?? null;

                if (!$path) {
                    throw new RuntimeException(
                        "Migration файлът '{$record->migration}' липсва и rollback не може да продължи."
                    );
                }

                $checksum = hash_file('sha256', $path);
                if (!hash_equals($record->checksum, $checksum)) {
                    throw new RuntimeException(
                        "Migration файлът '{$record->migration}' е променен и rollback е блокиран."
                    );
                }

                $instance = $this->loadMigration($path, $tablePrefix);

                $operation = function () use (
                    $instance,
                    $pluginSlug,
                    $record
                ): void {
                    $instance->down();
                    $this->repository->remove($pluginSlug, $record->migration);
                };

                $instance->shouldUseTransaction()
                    ? $this->connection->transaction($operation)
                    : $operation();

                $rolledBack[] = $record->migration;
            }

            return new PluginMigrationResult(
                pluginSlug: $pluginSlug,
                batch: $batch,
                migrations: $rolledBack,
                message: 'Последният migration batch беше върнат успешно.',
            );
        } finally {
            $this->releaseLock($pluginSlug);
        }
    }

    public function discover(string $migrationsPath): array
    {
        if (!is_dir($migrationsPath)) {
            return [];
        }

        $realPath = realpath($migrationsPath);
        if (!$realPath) {
            throw new RuntimeException('Migration директорията не може да бъде прочетена.');
        }

        $migrations = [];
        foreach (glob($realPath . DIRECTORY_SEPARATOR . '*.php') ?: [] as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);

            if (!preg_match('/^\d{14}_[a-z][a-z0-9_]*$/', $name)) {
                throw new RuntimeException("Невалидно име на plugin migration: {$name}.php");
            }

            $migrations[$name] = $file;
        }

        ksort($migrations, SORT_STRING);

        return $migrations;
    }

    private function loadMigration(string $path, string $tablePrefix): PluginMigrationInterface
    {
        $migration = require $path;

        if (!$migration instanceof PluginMigrationInterface) {
            throw new RuntimeException(
                "Migration файлът '{$path}' трябва да върне PluginMigrationInterface."
            );
        }

        $migration->configure($this->connection, $tablePrefix);

        return $migration;
    }

    private function validatePluginSlug(string $pluginSlug): void
    {
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $pluginSlug)) {
            throw new RuntimeException('Невалиден plugin slug.');
        }
    }

    private function acquireLock(string $pluginSlug): void
    {
        if ($this->connection->getDriverName() !== 'mysql') {
            return;
        }

        $lockName = 'flex_plugin_migrations_' . substr(hash('sha256', $pluginSlug), 0, 32);
        $result = $this->connection->selectOne(
            'SELECT GET_LOCK(?, 10) AS acquired',
            [$lockName]
        );

        if ((int) ($result->acquired ?? 0) !== 1) {
            throw new RuntimeException('Друг migration процес за този плъгин вече се изпълнява.');
        }
    }

    private function releaseLock(string $pluginSlug): void
    {
        if ($this->connection->getDriverName() !== 'mysql') {
            return;
        }

        $lockName = 'flex_plugin_migrations_' . substr(hash('sha256', $pluginSlug), 0, 32);
        $this->connection->selectOne('SELECT RELEASE_LOCK(?)', [$lockName]);
    }
}
