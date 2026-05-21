<?php

use Phinx\Seed\AbstractSeed;

class RoleAndPermissionSeeder extends AbstractSeed
{
    public function run(): void
    {
        $rolesData = [
            [
                'id' => 1,
                'name' => 'Administrator',
                'slug' => 'admin',
                'description' => 'Пълен достъп до системата',
                'priority' => 100,
                'color' => '#ef4444',
                'is_active' => 1,
                'is_default' => 0,
            ],
            [
                'id' => 2,
                'name' => 'Потребител',
                'slug' => 'user',
                'description' => 'Стандартен регистриран потребител',
                'priority' => 1,
                'color' => '#6366f1',
                'is_active' => 1,
                'is_default' => 1,
            ]
        ];

        $this->table('roles')->insert($rolesData)->saveData();

        $permissionsData = [
            ['id' => 1, 'name' => 'Преглед на таблото', 'slug' => 'admin.dashboard', 'module' => 'Core'],
            ['id' => 2, 'name' => 'Управление на потребители', 'slug' => 'admin.users', 'module' => 'Users'],
            ['id' => 3, 'name' => 'Управление на плъгини', 'slug' => 'admin.plugins', 'module' => 'Plugins'],
        ];

        $this->table('permissions')->insert($permissionsData)->saveData();

        $rolePermissionData = [
            ['role_id' => 1, 'permission_id' => 1],
            ['role_id' => 1, 'permission_id' => 2],
            ['role_id' => 1, 'permission_id' => 3],
        ];

        $this->table('role_permission')->insert($rolePermissionData)->saveData();
    }
}