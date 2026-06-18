<?php

use Phinx\Seed\AbstractSeed;

class SettingSeeder extends AbstractSeed
{
    public function run(): void
    {
        $settings = [
            ['key' => 'active_theme', 'value' => 'Basic', 'group' => 'system', 'type' => 'string'],
            ['key' => 'date_format', 'value' => 'l, j F Y', 'group' => 'general', 'type' => 'string'],
            ['key' => 'timezone', 'value' => 'Europe/Sofia', 'group' => 'general', 'type' => 'string'],
        ];

        $this->table('settings')->insert($settings)->saveData();
    }
}