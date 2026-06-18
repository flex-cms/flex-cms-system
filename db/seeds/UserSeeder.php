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

        $email = $_ENV['ADMIN_EMAIL'] ?? 'admin@example.com';
        $password = $_ENV['ADMIN_PASS'] ?? 'password';

        $adminData = [
            'username' => 'admin',
            'email'    => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
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
