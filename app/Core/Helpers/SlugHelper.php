<?php

namespace Flex\Core\Helpers;

class SlugHelper
{
    public static function generate(string $text): string
    {
        $cyrillic = ['а', 'б', 'в', 'г', 'д', 'е', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ь', 'ю', 'я'];
        $latin = ['a', 'b', 'v', 'g', 'd', 'e', 'zh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'ts', 'ch', 'sh', 'sht', 'a', 'y', 'yu', 'ya'];

        $text = str_replace($cyrillic, $latin, mb_strtolower($text));

        $text = preg_replace('/[^a-z0-9]+/i', '-', $text);

        $text = trim($text, '-');

        return $text;
    }
}