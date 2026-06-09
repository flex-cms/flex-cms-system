<?php

namespace Flex\Core\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SlugHelper
{
    public static function fromInput(
        ?string $slug,
        ?string $name,
        string $modelClass,
        string $column = 'slug',
        ?int $ignoreId = null
    ): string {
        $slug = ($slug && trim($slug) !== '') ? trim($slug) : null;
        $name = ($name && trim($name) !== '') ? trim($name) : null;

        $base = self::generate($slug ?: $name ?: '');

        if ($base === '') {
            return '';
        }

        return self::makeUnique($modelClass, $column, $base, $ignoreId);
    }

    protected static function makeUnique(
        string $modelClass,
        string $column,
        string $base,
        ?int $ignoreId = null
    ): string {
        $slug = $base;
        $counter = 1;

        while (true) {
            $query = $modelClass::query()->where($column, $slug);

            if (in_array(SoftDeletes::class, class_uses_recursive($modelClass))) {
                $query->withTrashed();
            }

            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }
            
            if (!$query->exists()) {
                return $slug;
            }
            $slug = $base . '-' . $counter;
            $counter++;
        }
    }

    public static function generate(string $text): string
    {
        $cyrillic = ['а', 'б', 'в', 'г', 'д', 'е', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ь', 'ю', 'я'];
        $latin = ['a', 'b', 'v', 'g', 'd', 'e', 'zh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'ts', 'ch', 'sh', 'sht', 'a', 'y', 'yu', 'ya'];

        $text = str_replace($cyrillic, $latin, mb_strtolower($text));
        $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
        return trim($text, '-');
    }
}