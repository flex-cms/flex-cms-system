<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Seeders;

use Flex\Core\Database\Seeder;
use Flex\Features\Authentication\Models\Role;

final class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Administrator',
                'slug' => 'administrator',
            ],
            [
                'name' => 'Editor',
                'slug' => 'editor',
            ],
            [
                'name' => 'User',
                'slug' => 'user',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                [
                    'slug' => $role['slug'],
                ],
                [
                    'name' => $role['name'],
                ],
            );
        }
    }
}
