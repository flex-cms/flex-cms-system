<?php

declare(strict_types=1);

use Flex\Core\UI\Form;

$settings = $settings ?? [];

$value = static function (
    string $key,
    mixed $default = null
) use ($settings): mixed {
    return array_key_exists($key, $settings)
        ? $settings[$key]
        : $default;
};
?>

<?php Form::section(
    title: 'Поведение на файловете',
    slot: function () use ($value): void {
        Form::row(function () use ($value): void {
            Form::toggle(
                'settings[media_use_date_folders]',
                'Подреждане в папки по дата',
                [
                    'value' => (bool) $value(
                        'media_use_date_folders',
                        true
                    ),
                    'description' =>
                        'Автоматично създава подпапки '
                        . '(година/месец/ден) при '
                        . 'качване на файлове.',
                ]
            );

            Form::toggle(
                'settings[media_keep_original_name]',
                'Запазване на оригиналното име',
                [
                    'value' => (bool) $value(
                        'media_keep_original_name',
                        false
                    ),
                    'description' =>
                        'Ако е изключено, имената се '
                        . 'променят на уникални '
                        . 'идентификатори.',
                ]
            );
        }, ['md' => 2]);
    }
); ?>

<?php Form::section(
    title: 'Ограничения и сигурност',
    slot: function () use ($value): void {
        Form::row(function () use ($value): void {
            Form::input(
                'settings[media_max_size]',
                'Максимален размер (MB)',
                [
                    'value' => $value(
                        'media_max_size',
                        5
                    ),
                    'type' => 'number',
                    'min' => 1,
                    'description' =>
                        'Максимален размер на файл '
                        . 'за качване в мегабайти.',
                ]
            );

            Form::input(
                'settings[media_allowed_extensions]',
                'Разрешени разширения',
                [
                    'value' => $value(
                        'media_allowed_extensions',
                        'jpg,png,webp'
                    ),
                    'placeholder' => 'jpg,png,webp,pdf',
                    'description' =>
                        'Изброени със запетая '
                        . '(например jpg,png,pdf).',
                ]
            );
        }, ['md' => 2]);
    }
); ?>