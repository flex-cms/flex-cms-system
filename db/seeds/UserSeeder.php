<?php

use Phinx\Seed\AbstractSeed;

class UserSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return [
            '_01_RoleAndPermissionSeeder'
        ];
    }

    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $pdo = $this->getAdapter()->getConnection();

        $adminData = [
            'username' => 'admin',
            'email' => 'admin@flex-cms.com',
            'password' => password_hash('admin123', PASSWORD_BCRYPT),
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $this->table('users')->insert($adminData)->saveData();

        $adminId = $pdo->lastInsertId();

        $this->table('user_role')->insert([
            'user_id' => $adminId,
            'role_id' => 1
        ])->saveData();
    }
}
