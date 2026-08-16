<?php

declare(strict_types=1);

namespace Flex\Features\Authentication\Seeders;

use Flex\Core\Database\Seeder;
use Flex\Features\Authentication\Factories\UserFactory;

final class UserSeeder extends Seeder
{
    public function run(): void
    {
        UserFactory::new()->count(100)->create();
    }
}
