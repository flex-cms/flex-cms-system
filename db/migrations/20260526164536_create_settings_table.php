<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class CreateSettingsTable extends AbstractMigration
{
    public function change(): void
    {
        $settings = $this->table('settings');
        $settings->addColumn('key', 'string', ['limit' => 100])
            ->addColumn('value', 'text', ['null' => true])
            ->addColumn('group', 'string', ['limit' => 50, 'default' => 'general'])
            ->addColumn('type', 'string', ['limit' => 20, 'default' => 'string'])
            ->addColumn('options', 'json', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['null' => true, 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['key'], ['unique' => true])
            ->addIndex(['group'])
            ->create();
    }
}
