<?php

namespace Flex\Models;

class Theme
{
    public static function all(): array
    {
        $themes = [];
        $themesDir = themes_path();

        if (!is_dir($themesDir)) {
            return [];
        }

        foreach (scandir($themesDir) as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }

            $jsonPath = $themesDir . '/' . $dir . '/theme.json';

            if (file_exists($jsonPath)) {
                $content = file_get_contents($jsonPath);
                $config = json_decode($content, true);

                if (is_array($config)) {
                    $config['folder'] = $dir;
                    $config['is_active'] = (defined('ACTIVE_THEME') && ACTIVE_THEME === $dir);

                    $config['screenshot'] = file_exists($themesDir . '/' . $dir . '/screenshot.png')
                        ? '/themes/' . $dir . '/screenshot.png'
                        : null;

                    $themes[] = (object) $config;
                }
            }
        }
        return $themes;
    }
}
