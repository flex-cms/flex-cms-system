<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUsersTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('users');
        $table->addColumn('fullname', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('email', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('password', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('role', 'enum', ['values' => ['admin', 'editor', 'user'], 'default' => 'user'])
            ->addColumn('remember_token', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('is_active', 'boolean', ['default' => true, 'null' => false])
            ->addColumn('options', 'json', ['null' => true])
            ->addColumn('last_login', 'timestamp', ['null' => true])
            ->addColumn('deleted_at', 'timestamp', ['null' => true])
            ->addTimestamps()
            ->addIndex(['username'], ['unique' => true, 'name' => 'idx_users_username'])
            ->addIndex(['email'], ['unique' => true, 'name' => 'idx_users_email'])
            ->create();
    }
}