<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePluginsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('plugins');
        $table->addColumn('name', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('slug', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('author', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('author_url', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('requires', 'json', ['null' => true])
            ->addColumn('is_active', 'boolean', ['default' => false, 'null' => false])
            ->addColumn('is_installed', 'boolean', ['default' => false, 'after' => 'is_active'])
            ->addColumn('version', 'string', ['limit' => 20, 'default' => null, 'null' => true])
            ->addTimestamps()
            ->addIndex(['slug'], ['unique' => true, 'name' => 'idx_plugins_slug'])
            ->create();
    }
}
