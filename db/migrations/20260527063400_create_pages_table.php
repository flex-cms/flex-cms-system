<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

class CreatePagesTable extends AbstractMigration
{
    public function change(): void
    {
        $pages = $this->table('pages');

        $pages
            ->addColumn('name', 'string', ['limit' => 255])
            ->addColumn('slug', 'string', ['limit' => 255])
            ->addColumn('full_slug', 'string', [
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('parent_id', 'integer', [
                'null' => true,
                'default' => null,
                'signed' => false,
            ])
            ->addColumn('position', 'integer', [
                'default' => 0,
                'signed' => false,
            ])
            ->addColumn('created_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
            ])
            ->addColumn('updated_at', 'timestamp', [
                'null' => true,
                'update' => 'CURRENT_TIMESTAMP',
            ])
            ->addColumn('deleted_at', 'timestamp', [
                'null' => true,
            ])
            ->addColumn('is_active', 'boolean', [
                'default' => true,
                'null' => false,
            ])
            ->addIndex(['slug'], ['unique' => true])
            ->addIndex(['full_slug'])
            ->addIndex(['parent_id'])
            ->create();

        $pageOptions = $this->table('page_options');

        $pageOptions
            ->addColumn('page_id', 'integer', [
                'signed' => false,
            ])
            ->addColumn('option_key', 'string', [
                'limit' => 255,
            ])
            ->addColumn('option_value', 'text', [
                'null' => true,
            ])
            ->addColumn('created_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
            ])
            ->addColumn('updated_at', 'timestamp', [
                'null' => true,
                'update' => 'CURRENT_TIMESTAMP',
            ])
            ->addIndex(['page_id'])
            ->addIndex(
                ['page_id', 'option_key'],
                ['unique' => true]
            )
            ->addForeignKey(
                'page_id',
                'pages',
                'id',
                [
                    'delete' => 'CASCADE',
                    'update' => 'CASCADE',
                ]
            )
            ->create();

        $pageElements = $this->table('page_elements');

        $pageElements
            ->addColumn('page_id', 'integer', [
                'signed' => false,
            ])
            ->addColumn('parent_id', 'integer', [
                'null' => true,
                'default' => null,
                'signed' => false,
            ])
            ->addColumn('element_type', 'string', [
                'limit' => 100,
            ])
            ->addColumn('position', 'integer', [
                'default' => 0,
                'signed' => false,
            ])
            ->addColumn('settings', 'json', [
                'null' => true,
            ])
            ->addColumn('created_at', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
            ])
            ->addColumn('updated_at', 'timestamp', [
                'null' => true,
                'update' => 'CURRENT_TIMESTAMP',
            ])
            ->addIndex(['page_id'])
            ->addIndex(['parent_id'])
            ->addIndex(['page_id', 'parent_id', 'position'])
            ->addForeignKey(
                'page_id',
                'pages',
                'id',
                [
                    'delete' => 'CASCADE',
                    'update' => 'CASCADE',
                ]
            )
            ->addForeignKey(
                'parent_id',
                'page_elements',
                'id',
                [
                    'delete' => 'CASCADE',
                    'update' => 'CASCADE',
                ]
            )
            ->create();
    }
}