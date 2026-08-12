<?php

declare(strict_types=1);

namespace Flex\Core\Support;

final class Slug
{
    private const CYRILLIC_MAP = [
        'а' => 'a',
        'б' => 'b',
        'в' => 'v',
        'г' => 'g',
        'д' => 'd',
        'е' => 'e',
        'ж' => 'zh',
        'з' => 'z',
        'и' => 'i',
        'й' => 'y',
        'к' => 'k',
        'л' => 'l',
        'м' => 'm',
        'н' => 'n',
        'о' => 'o',
        'п' => 'p',
        'р' => 'r',
        'с' => 's',
        'т' => 't',
        'у' => 'u',
        'ф' => 'f',
        'х' => 'h',
        'ц' => 'ts',
        'ч' => 'ch',
        'ш' => 'sh',
        'щ' => 'sht',
        'ъ' => 'a',
        'ь' => 'y',
        'ю' => 'yu',
        'я' => 'ya',
    ];

    public static function make(
        string $value,
        string $separator = '-'
    ): string {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        $value = mb_strtolower(
            $value,
            'UTF-8'
        );

        $value = strtr(
            $value,
            self::CYRILLIC_MAP
        );

        $value = preg_replace(
            '/[^a-z0-9]+/u',
            $separator,
            $value
        ) ?? '';

        return trim(
            $value,
            $separator
        );
    }
}
