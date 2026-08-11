<?php

declare(strict_types=1);

namespace Flex\Core\Features\Migrations;

use Flex\Core\Features\Migrations\Exceptions\FeatureNotFoundException;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

final class FeatureMigrationRunner
{
    private const MIGRATIONS_TABLE = 'flex_feature_migrations';

    public function __construct(
        private readonly Connection $connection,
        private readonly string $projectPath
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function migrate(string $featureName): array
    {
        $featureName = $this->normalizeFeatureName(
            $featureName
        );

        $featurePath = $this->resolveFeaturePath(
            $featureName
        );

        $migrationsPath = $featurePath
            . DIRECTORY_SEPARATOR
            . 'Migrations';

        if (!is_dir($migrationsPath)) {
            return [];
        }

        $this->ensureMigrationsTable();

        $files = glob(
            $migrationsPath
            . DIRECTORY_SEPARATOR
            . '*.php'
        );

        if ($files === false || $files === []) {
            return [];
        }

        sort($files, SORT_STRING);

        $executed = $this->executedMigrationNames(
            $featureName
        );

        $pending = array_values(
            array_filter(
                $files,
                static fn (string $file): bool =>
                    !in_array(
                        pathinfo($file, PATHINFO_FILENAME),
                        $executed,
                        true
                    )
            )
        );

        if ($pending === []) {
            return [];
        }

        $batch = $this->nextBatch(
            $featureName
        );

        $migrated = [];

        foreach ($pending as $file) {
            $migrationName = pathinfo(
                $file,
                PATHINFO_FILENAME
            );

            $className = $this->classNameFromMigrationName(
                $migrationName
            );

            try {
                require_once $file;

                if (!class_exists($className)) {
                    throw new RuntimeException(
                        sprintf(
                            'Migration класът "%s" не беше намерен във файла "%s".',
                            $className,
                            $file
                        )
                    );
                }

                $migration = $this->createMigrationInstance(
                    $className
                );

                if (!method_exists($migration, 'up')) {
                    throw new RuntimeException(
                        sprintf(
                            'Migration "%s" трябва да поддържа публичен метод up().',
                            $migrationName
                        )
                    );
                }

                $migration->up();

                $this->connection
                    ->table(self::MIGRATIONS_TABLE)
                    ->insert([
                        'feature' => $featureName,
                        'migration' => $migrationName,
                        'batch' => $batch,
                        'executed_at' => date('Y-m-d H:i:s'),
                    ]);

                $migrated[] = $migrationName;
            } catch (Throwable $exception) {
                throw new RuntimeException(
                    sprintf(
                        'Migration "%s" се провали: %s',
                        $migrationName,
                        $exception->getMessage()
                    ),
                    previous: $exception
                );
            }
        }

        return $migrated;
    }

    /**
     * @return array<int, array{
     *     migration: string,
     *     executed: bool,
     *     batch: int|null,
     *     executed_at: string|null
     * }>
     */
    public function status(string $featureName): array
    {
        $featureName = $this->normalizeFeatureName(
            $featureName
        );

        $featurePath = $this->resolveFeaturePath(
            $featureName
        );

        $migrationsPath = $featurePath
            . DIRECTORY_SEPARATOR
            . 'Migrations';

        if (!is_dir($migrationsPath)) {
            return [];
        }

        $files = glob(
            $migrationsPath
            . DIRECTORY_SEPARATOR
            . '*.php'
        );

        if ($files === false || $files === []) {
            return [];
        }

        sort($files, SORT_STRING);

        $this->ensureMigrationsTable();

        $executed = $this->connection
            ->table(self::MIGRATIONS_TABLE)
            ->where('feature', $featureName)
            ->get()
            ->keyBy('migration');

        $result = [];

        foreach ($files as $file) {
            $migrationName = pathinfo(
                $file,
                PATHINFO_FILENAME
            );

            $record = $executed->get(
                $migrationName
            );

            $result[] = [
                'migration' => $migrationName,
                'executed' => $record !== null,
                'batch' => $record !== null
                    ? (int) $record->batch
                    : null,
                'executed_at' => $record !== null
                    ? (string) $record->executed_at
                    : null,
            ];
        }

        return $result;
    }

    /**
     * Връща последния batch миграции за feature.
     *
     * @return array<int, string>
     */
    public function rollback(string $featureName): array
    {
        $featureName = $this->normalizeFeatureName(
            $featureName
        );

        $featurePath = $this->resolveFeaturePath(
            $featureName
        );

        $migrationsPath = $featurePath
            . DIRECTORY_SEPARATOR
            . 'Migrations';

        if (!is_dir($migrationsPath)) {
            return [];
        }

        $this->ensureMigrationsTable();

        $lastBatch = $this->connection
            ->table(self::MIGRATIONS_TABLE)
            ->where('feature', $featureName)
            ->max('batch');

        if ($lastBatch === null) {
            return [];
        }

        $records = $this->connection
            ->table(self::MIGRATIONS_TABLE)
            ->where('feature', $featureName)
            ->where('batch', (int) $lastBatch)
            ->orderByDesc('id')
            ->get();

        if ($records->isEmpty()) {
            return [];
        }

        $rolledBack = [];

        foreach ($records as $record) {
            $migrationName = (string) $record->migration;

            $file = $migrationsPath
                . DIRECTORY_SEPARATOR
                . $migrationName
                . '.php';

            if (!is_file($file)) {
                throw new RuntimeException(
                    sprintf(
                        'Migration файлът "%s" не беше намерен.',
                        $file
                    )
                );
            }

            $className = $this->classNameFromMigrationName(
                $migrationName
            );

            try {
                require_once $file;

                if (!class_exists($className)) {
                    throw new RuntimeException(
                        sprintf(
                            'Migration класът "%s" не беше намерен във файла "%s".',
                            $className,
                            $file
                        )
                    );
                }

                $migration = $this->createMigrationInstance(
                    $className
                );

                if (!method_exists($migration, 'down')) {
                    throw new RuntimeException(
                        sprintf(
                            'Migration "%s" не поддържа rollback чрез down().',
                            $migrationName
                        )
                    );
                }

                $migration->down();

                $this->connection
                    ->table(self::MIGRATIONS_TABLE)
                    ->where('id', (int) $record->id)
                    ->delete();

                $rolledBack[] = $migrationName;
            } catch (Throwable $exception) {
                throw new RuntimeException(
                    sprintf(
                        'Rollback на migration "%s" се провали: %s',
                        $migrationName,
                        $exception->getMessage()
                    ),
                    previous: $exception
                );
            }
        }

        return $rolledBack;
    }

    /**
     * Връща всички изпълнени batch-ове на feature.
     *
     * @return array<int, string>
     */
    public function rollbackAll(string $featureName): array
    {
        $featureName = $this->normalizeFeatureName(
            $featureName
        );

        $rolledBack = [];

        while (true) {
            $batch = $this->rollback(
                featureName: $featureName
            );

            if ($batch === []) {
                break;
            }

            foreach ($batch as $migrationName) {
                $rolledBack[] = $migrationName;
            }
        }

        return $rolledBack;
    }

    /**
     * Връща всички миграции и ги изпълнява отново.
     *
     * @return array{
     *     rolled_back: array<int, string>,
     *     migrated: array<int, string>
     * }
     */
    public function fresh(string $featureName): array
    {
        $rolledBack = $this->rollbackAll(
            featureName: $featureName
        );

        $migrated = $this->migrate(
            featureName: $featureName
        );

        return [
            'rolled_back' => $rolledBack,
            'migrated' => $migrated,
        ];
    }

    /**
     * Намира всички feature-и, които имат Migrations директория.
     *
     * @return array<int, string>
     */
    public function discoverFeaturesWithMigrations(): array
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

        $entries = scandir(
            $featuresRoot
        );

        if ($entries === false) {
            return [];
        }

        $features = [];

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $featurePath = $featuresRoot
                . DIRECTORY_SEPARATOR
                . $entry;

            if (!is_dir($featurePath)) {
                continue;
            }

            $migrationsPath = $featurePath
                . DIRECTORY_SEPARATOR
                . 'Migrations';

            if (!is_dir($migrationsPath)) {
                continue;
            }

            $files = glob(
                $migrationsPath
                . DIRECTORY_SEPARATOR
                . '*.php'
            );

            if ($files === false || $files === []) {
                continue;
            }

            $features[] = $entry;
        }

        sort(
            $features,
            SORT_NATURAL | SORT_FLAG_CASE
        );

        return $features;
    }

    private function createMigrationInstance(
        string $className
    ): object {
        $reflection = new \ReflectionClass(
            $className
        );

        $constructor = $reflection->getConstructor();

        if (
            $constructor === null
            || $constructor->getNumberOfRequiredParameters() === 0
        ) {
            return $reflection->newInstance();
        }

        throw new RuntimeException(
            sprintf(
                'Migration класът "%s" има constructor с параметри. '
                . 'Feature migrations трябва да могат да бъдат създадени без аргументи.',
                $className
            )
        );
    }

    private function ensureMigrationsTable(): void
    {
        $schema = $this->connection->getSchemaBuilder();

        if ($schema->hasTable(self::MIGRATIONS_TABLE)) {
            return;
        }

        $schema->create(
            self::MIGRATIONS_TABLE,
            static function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->string('feature', 120);
                $table->string('migration', 255);
                $table->unsignedInteger('batch');
                $table->timestamp('executed_at')->nullable();

                $table->unique(
                    ['feature', 'migration'],
                    'flex_feature_migrations_unique'
                );

                $table->index(
                    ['feature', 'batch'],
                    'flex_feature_migrations_batch'
                );
            }
        );
    }

    /**
     * @return array<int, string>
     */
    private function executedMigrationNames(
        string $featureName
    ): array {
        return $this->connection
            ->table(self::MIGRATIONS_TABLE)
            ->where('feature', $featureName)
            ->orderBy('id')
            ->pluck('migration')
            ->map(
                static fn ($migration): string =>
                    (string) $migration
            )
            ->all();
    }

    private function nextBatch(
        string $featureName
    ): int {
        $maxBatch = $this->connection
            ->table(self::MIGRATIONS_TABLE)
            ->where('feature', $featureName)
            ->max('batch');

        return ((int) $maxBatch) + 1;
    }

    private function classNameFromMigrationName(
        string $migrationName
    ): string {
        $name = preg_replace(
            '/^\d+_/',
            '',
            $migrationName
        );

        if (!is_string($name) || $name === '') {
            throw new RuntimeException(
                sprintf(
                    'Невалидно име на migration: %s',
                    $migrationName
                )
            );
        }

        return str_replace(
            ' ',
            '',
            ucwords(
                str_replace(
                    ['-', '_'],
                    ' ',
                    $name
                )
            )
        );
    }

    private function normalizeFeatureName(
        string $featureName
    ): string {
        $featureName = trim(
            $featureName
        );

        if ($featureName === '') {
            throw new InvalidArgumentException(
                'Името на feature не може да бъде празно.'
            );
        }

        if (
            !preg_match(
                '/^[a-z0-9_-]+$/i',
                $featureName
            )
        ) {
            throw new InvalidArgumentException(
                'Името на feature може да съдържа само '
                . 'букви, цифри, тире и долна черта.'
            );
        }

        return $featureName;
    }

    private function resolveFeaturePath(
        string $featureName
    ): string {
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
            . $featureName;

        if (is_dir($directPath)) {
            return $directPath;
        }

        $entries = scandir(
            $featuresRoot
        );

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
                    && strcasecmp(
                        $entry,
                        $featureName
                    ) === 0
                ) {
                    return $candidate;
                }
            }
        }

        throw new FeatureNotFoundException(
            sprintf(
                'Feature "%s" не беше намерен в: %s',
                $featureName,
                $featuresRoot
            )
        );
    }
}
