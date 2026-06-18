<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

class DatabaseSeeder extends AbstractSeed
{
    public function run(): void
    {
        $seeders = [
            RoleAndPermissionSeeder::class,
            UserSeeder::class,
            EmailTemplateSeeder::class,
            SettingSeeder::class,
        ];

        foreach ($seeders as $seederClass) {
            $seeder = new $seederClass();
            $seeder->setAdapter($this->getAdapter());
            $seeder->run();
        }
    }
}