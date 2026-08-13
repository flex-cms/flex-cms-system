<?php

declare(strict_types=1);

namespace Flex\Features\Pages\Data;

enum PageFieldType: string
{
    case Text = 'text';
    case Textarea = 'textarea';
    case RichText = 'rich_text';
    case Image = 'image';
    case Gallery = 'gallery';

    public function label(): string
    {
        return match ($this) {
            self::Text => 'Кратък текст',
            self::Textarea => 'Дълъг текст',
            self::RichText => 'Визуален редактор',
            self::Image => 'Изображение',
            self::Gallery => 'Галерия',
        };
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $type) {
            $options[$type->value] = $type->label();
        }

        return $options;
    }
}
