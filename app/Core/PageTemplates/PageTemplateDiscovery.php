<?php

namespace Flex\Core\PageTemplates;

use Flex\Core\PageTemplates\Attributes\PageTemplate;
use ReflectionClass;

class PageTemplateDiscovery
{
    public static function getTemplates(string $theme): array
    {
        $path = themes_path("{$theme}/PageTemplates");

        $templates = [];

        foreach (glob($path . '/*Template.php') as $file) {

            $class = self::classFromFile($file, $theme);

            if (!class_exists($class)) {
                continue;
            }

            $ref = new ReflectionClass($class);

            $attributes = $ref->getAttributes(PageTemplate::class);

            if (!$attributes) {
                continue;
            }

            $meta = $attributes[0]->newInstance();

            $templates[$meta->key] = $meta->name;
        }

        return $templates;
    }

    protected static function classFromFile(string $file, string $theme): string
    {
        $class = basename($file, '.php');
        return "\\Themes\\{$theme}\\PageTemplates\\{$class}";
    }
}
