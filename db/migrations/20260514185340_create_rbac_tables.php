<?php

use Phinx\Migration\AbstractMigration;

class CreateRbacTables extends AbstractMigration
{
    public function change(): void
    {
        $roles = $this->table('roles');
        $roles->addColumn('name', 'string', ['limit' => 50])
            ->addColumn('slug', 'string', ['limit' => 50])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('priority', 'integer', ['default' => 0, 'null' => false])
            ->addColumn('color', 'string', ['limit' => 7, 'default' => '#6366f1'])
            ->addColumn('is_active', 'boolean', ['default' => true])
            ->addColumn('is_default', 'boolean', ['default' => false])
            ->addColumn('options', 'json', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['null' => true, 'update' => 'CURRENT_TIMESTAMP'])
            ->addColumn('deleted_at', 'timestamp', ['null' => true])
            ->addIndex(['slug'], ['unique' => true])
            ->addIndex(['priority'])
            ->create();

        $permissions = $this->table('permissions');
        $permissions->addColumn('name', 'string', ['limit' => 50])
            ->addColumn('slug', 'string', ['limit' => 50])
            ->addColumn('module', 'string', ['limit' => 50])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('is_active', 'boolean', ['default' => true, 'null' => false])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['null' => true, 'update' => 'CURRENT_TIMESTAMP'])
            ->addColumn('deleted_at', 'timestamp', ['null' => true])
            ->addIndex(['slug'], ['unique' => true])
            ->create();

        $rolePermission = $this->table('role_permission', ['id' => false, 'primary_key' => ['role_id', 'permission_id']]);
        $rolePermission->addColumn('role_id', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('permission_id', 'integer', ['signed' => false, 'null' => false])
            ->addForeignKey('role_id', 'roles', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('permission_id', 'permissions', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();

        $userRole = $this->table('user_role', ['id' => false, 'primary_key' => ['user_id', 'role_id']]);
        $userRole->addColumn('user_id', 'integer', ['signed' => false, 'null' => false])
            ->addColumn('role_id', 'integer', ['signed' => false, 'null' => false])
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->addForeignKey('role_id', 'roles', 'id', ['delete' => 'CASCADE', 'update' => 'CASCADE'])
            ->create();

        $users = $this->table('users');
        if ($users->hasColumn('role')) {
            $users->removeColumn('role')->update();
        }
    }
}
