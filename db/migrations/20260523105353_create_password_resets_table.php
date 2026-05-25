<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreatePasswordResetsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('password_resets', [
            'id' => false,
            'primary_key' => 'email'
        ]);

        $table->addColumn('email', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('token', 'string', ['limit' => 64, 'null' => false])
            ->addColumn('expires_at', 'datetime', ['null' => false])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'null' => false])
            ->create();
    }
}