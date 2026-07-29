<?php

namespace Flex\Core\Plugins\Migrations;

use Illuminate\Database\Connection;
use Illuminate\Support\Collection;

final class PluginMigrationRepository
{
    public function __construct(
        private readonly Connection $connection,
        private readonly string $table = 'plugin_migrations',
    ) {
    }

    public function completed(string $pluginSlug): array
    {
        return $this->query()
            ->where('plugin_slug', $pluginSlug)
            ->where('status', 'completed')
            ->pluck('checksum', 'migration')
            ->all();
    }

    public function nextBatch(string $pluginSlug): int
    {
        return (int) $this->query()
            ->where('plugin_slug', $pluginSlug)
            ->max('batch') + 1;
    }

    public function markRunning(
        string $pluginSlug,
        string $migration,
        string $pluginVersion,
        int $batch,
        string $checksum,
    ): void {
        $this->query()->updateOrInsert(
            [
                'plugin_slug' => $pluginSlug,
                'migration' => $migration,
            ],
            [
                'plugin_version' => $pluginVersion,
                'batch' => $batch,
                'checksum' => $checksum,
                'status' => 'running',
                'error_message' => null,
                'execution_time_ms' => null,
                'executed_at' => null,
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        );
    }

    public function markCompleted(
        string $pluginSlug,
        string $migration,
        int $executionTimeMs,
    ): void {
        $this->forMigration($pluginSlug, $migration)->update([
            'status' => 'completed',
            'error_message' => null,
            'execution_time_ms' => $executionTimeMs,
            'executed_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function markFailed(
        string $pluginSlug,
        string $migration,
        string $message,
    ): void {
        $this->forMigration($pluginSlug, $migration)->update([
            'status' => 'failed',
            'error_message' => mb_substr($message, 0, 65535),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function latestCompletedBatch(string $pluginSlug): int
    {
        return (int) $this->query()
            ->where('plugin_slug', $pluginSlug)
            ->where('status', 'completed')
            ->max('batch');
    }

    public function completedInBatch(string $pluginSlug, int $batch): Collection
    {
        return $this->query()
            ->where('plugin_slug', $pluginSlug)
            ->where('batch', $batch)
            ->where('status', 'completed')
            ->orderByDesc('id')
            ->get();
    }

    public function remove(string $pluginSlug, string $migration): void
    {
        $this->forMigration($pluginSlug, $migration)->delete();
    }

    private function query()
    {
        return $this->connection->table($this->table);
    }

    private function forMigration(string $pluginSlug, string $migration)
    {
        return $this->query()
            ->where('plugin_slug', $pluginSlug)
            ->where('migration', $migration);
    }
}