<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Seeders;

use Flex\Core\Database\Seeder;

final class AuthenticationSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
        ]);
    }
}
