<?php

use Phinx\Seed\AbstractSeed;

class SettingSeeder extends AbstractSeed
{
    public function run(): void
    {
        $email = $_ENV['ADMIN_EMAIL'] ?? 'admin@example.com';

        $settings = [
            ['key' => 'active_theme', 'value' => 'Basic', 'group' => 'system', 'type' => 'string'],
            ['key' => 'date_format', 'value' => 'l, j F Y', 'group' => 'general', 'type' => 'string'],
            ['key' => 'timezone', 'value' => 'Europe/Sofia', 'group' => 'general', 'type' => 'string'],
            ['key' => 'admin_email', 'value' => $email, 'group' => 'general', 'type' => 'string'],
            ['key' => 'debug_mode', 'value' => 1, 'group' => 'general', 'type' => 'boolean'],
        ];

        $this->table('settings')->insert($settings)->saveData();
    }
}
