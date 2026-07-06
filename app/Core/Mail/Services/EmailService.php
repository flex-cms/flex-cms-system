<?php

namespace Flex\Core\Mail\Services;

class EmailService
{
    public static function extractVariables(string $content): array
    {
        preg_match_all('/\{\{([\w\.]+)\}\}/', $content, $matches);
        return array_unique($matches[1]);
    }

    public static function render(string $template, array $data): string
    {
        foreach ($data as $key => $value) {
            $template = str_replace("{{{$key}}}", $value, $template);
        }
        return $template;
    }
}