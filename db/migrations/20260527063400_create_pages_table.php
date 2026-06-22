<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class CreatePagesTable extends AbstractMigration
{
    public function change(): void
    {
        $pages = $this->table('pages');
        $pages->addColumn('name', 'string', ['limit' => 255])
            ->addColumn('slug', 'string', ['limit' => 255])
            ->addColumn('full_slug', 'string', ['limit' => 255, 'null' => true])
            ->addColumn('parent_id', 'integer', ['null' => true, 'default' => null])
            ->addColumn('options', 'json', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['null' => true, 'update' => 'CURRENT_TIMESTAMP'])
            ->addColumn('deleted_at', 'timestamp', ['null' => true])
            ->addColumn('is_active', 'boolean', ['default' => true, 'null' => false])

            ->addIndex(['slug'], ['unique' => true])
            ->addIndex(['full_slug'])
            ->addIndex(['parent_id'])
            ->create();
    }
}