<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePluginMigrationsTable extends AbstractMigration
{
    public function change(): void
    {
        $this->table('plugin_migrations')
            ->addColumn('plugin_slug', 'string', ['limit' => 100])
            ->addColumn('migration', 'string', ['limit' => 255])
            ->addColumn('plugin_version', 'string', ['limit' => 20])
            ->addColumn('batch', 'integer', ['default' => 1])
            ->addColumn('checksum', 'string', ['limit' => 64])
            ->addColumn('status', 'enum', [
                'values' => ['running', 'completed', 'failed'],
                'default' => 'running',
            ])
            ->addColumn('execution_time_ms', 'integer', ['null' => true])
            ->addColumn('error_message', 'text', ['null' => true])
            ->addColumn('executed_at', 'timestamp', ['null' => true])
            ->addTimestamps()
            ->addIndex(['plugin_slug', 'migration'], [
                'unique' => true,
                'name' => 'plugin_migrations_unique',
            ])
            ->addIndex(['plugin_slug', 'batch'], [
                'name' => 'plugin_migrations_batch',
            ])
            ->create();
    }
}