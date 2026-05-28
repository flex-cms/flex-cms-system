<?php

namespace Flex\Models;

class Theme
{
    public static function all(): array
    {
        $themes = [];
        $themesDir = dirname(__DIR__, 2) . '/themes';

        if (!is_dir($themesDir))
            return [];

        foreach (scandir($themesDir) as $dir) {
            if ($dir === '.' || $dir === '..')
                continue;

            $configPath = $themesDir . '/' . $dir . '/config.php';
            if (file_exists($configPath)) {
                $config = include $configPath;
                $config['folder'] = $dir;
                $config['is_active'] = (defined('ACTIVE_THEME') && ACTIVE_THEME === $dir);
                $themes[] = (object) $config;
            }
        }
        return $themes;
    }
}