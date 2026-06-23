<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class EmailTemplatesTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('email_templates', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci'
        ]);

        $table->addColumn('id', 'biginteger', ['identity' => true])
              ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
              ->addColumn('slug', 'string', ['limit' => 255, 'null' => false])
              ->addColumn('category', 'string', ['limit' => 255, 'null' => false])
              ->addColumn('subject', 'string', ['limit' => 255, 'null' => false])
              ->addColumn('body', 'text', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_LONG, 'null' => false])
              ->addColumn('variables', 'json', ['null' => true])
              ->addColumn('created_at', 'timestamp', ['null' => true, 'default' => null])
              ->addColumn('updated_at', 'timestamp', ['null' => true, 'default' => null])
              ->addColumn('deleted_at', 'timestamp', ['null' => true])
              ->addColumn('is_active', 'boolean', ['default' => true, 'null' => false])
              ->addIndex(['slug'], ['unique' => true, 'name' => 'email_templates_slug_unique'])
              ->create();
    }
}
