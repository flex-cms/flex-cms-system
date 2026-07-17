<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateMenusAndMenuItemsTables extends AbstractMigration
{
    public function change(): void
    {
        $menusTable = $this->table('menus', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci'
        ]);

        $menusTable->addColumn('id', 'biginteger', ['identity' => true])
            ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('slug', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('is_active', 'boolean', ['default' => 1, 'null' => false])
            ->addColumn('created_at', 'timestamp', ['null' => true, 'default' => null])
            ->addColumn('updated_at', 'timestamp', ['null' => true, 'default' => null])
            ->addColumn('deleted_at', 'timestamp', ['null' => true])
            ->addIndex(['slug'], ['unique' => true, 'name' => 'menus_slug_unique'])
            ->create();

        $menuItemsTable = $this->table('menu_items', [
            'id' => false,
            'primary_key' => ['id'],
            'engine' => 'InnoDB',
            'encoding' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci'
        ]);

        $menuItemsTable->addColumn('id', 'biginteger', ['identity' => true])
            ->addColumn('menu_id', 'biginteger', ['null' => false])
            ->addColumn('parent_id', 'biginteger', ['null' => true, 'default' => null])
            ->addColumn('title', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('is_active', 'boolean', ['default' => 1, 'null' => false])
            ->addColumn('url', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('target', 'string', ['limit' => 50, 'default' => '_self', 'null' => false])
            ->addColumn('order', 'integer', ['default' => 0, 'null' => false])
            ->addColumn('created_at', 'timestamp', ['null' => true, 'default' => null])
            ->addColumn('updated_at', 'timestamp', ['null' => true, 'default' => null])

            ->addForeignKey('menu_id', 'menus', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])
            ->addForeignKey('parent_id', 'menu_items', 'id', [
                'delete' => 'CASCADE',
                'update' => 'NO_ACTION',
            ])

            ->addIndex(['menu_id'], ['name' => 'idx_menu_items_menu_id'])
            ->addIndex(['parent_id'], ['name' => 'idx_menu_items_parent_id'])
            ->create();
    }
}