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
        $passwordHash = password_hash('password123', PASSWORD_BCRYPT);

        // --- 1. СЪЗДАВАНЕ НА ГЛАВНИЯ АДМИН ---
        $adminData = [
            'username'   => 'admin',
            'email'      => 'admin@flex-cms.com',
            'password'   => password_hash('admin123', PASSWORD_BCRYPT), // Негова специфична парола
            'is_active'  => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        $this->table('users')->insert($adminData)->saveData();
        
        $adminRow = $this->getAdapter()->fetchRow("SELECT id FROM users WHERE email = 'admin@flex-cms.com'");
        if ($adminRow) {
            $this->table('user_role')->insert([
                'user_id' => $adminRow['id'],
                'role_id' => 1
            ])->saveData();
        }

        $usersBatch = [];
        
        if (class_exists(\Faker\Factory::class)) {
            $faker = \Faker\Factory::create('bg_BG');
            
            for ($i = 1; $i <= 100; $i++) {
                $usersBatch[] = [
                    'username'   => $faker->unique()->userName,
                    'email'      => $faker->unique()->safeEmail,
                    'password'   => $passwordHash,
                    'is_active'  => rand(0, 10) > 1 ? 1 : 0,
                    'created_at' => date('Y-m-d H:i:s', strtotime("-" . rand(1, 30) . " days")),
                    'updated_at' => $now,
                ];
            }
        } else {
            for ($i = 1; $i <= 100; $i++) {
                $usersBatch[] = [
                    'username'   => "user_{$i}",
                    'email'      => "user{$i}@example.com",
                    'password'   => $passwordHash,
                    'is_active'  => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        $this->table('users')->insert($usersBatch)->saveData();

        $allUsers = $this->getAdapter()->fetchAll("SELECT id FROM users WHERE username != 'admin'");
        $userRoleRelations = [];

        foreach ($allUsers as $user) {
            $userRoleRelations[] = [
                'user_id' => $user['id'],
                'role_id' => 2
            ];
        }

        $this->table('user_role')->insert($userRoleRelations)->saveData();
    }
}
