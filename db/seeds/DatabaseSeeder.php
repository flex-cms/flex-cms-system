<?php

use Phinx\Seed\AbstractSeed;

class DatabaseSeeder extends AbstractSeed
{
    public function run(): void
    {
        $roleSeeder = new RoleAndPermissionSeeder();
        $roleSeeder->setAdapter($this->getAdapter());
        $roleSeeder->run();

        $userSeeder = new UserSeeder();
        $userSeeder->setAdapter($this->getAdapter());
        $userSeeder->run();
    }
}