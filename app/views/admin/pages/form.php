<?php

use Flex\Core\PageTemplates\PageTemplateDiscovery;
use Flex\Core\Routing\View;
use Flex\Core\UI\Form;
use Flex\Core\UI\Page;

$isEdit = isset($page->id);
$action = $isEdit
    ? "/admin/pages/update/{$page->id}"
    : '/admin/pages/store';

$options = [];

if ($isEdit) {
    foreach ($page->pageOptions as $option) {
        $value = $option->option_value;

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }
        }

        $options[$option->option_key] = $value;
    }
}

$page->setAttribute('options', $options);

Page::header(
    title: $isEdit
    ? 'Редактиране на страница'
    : 'Създаване на страница',
    backUrl: '/admin/pages',
    subtitle: $isEdit
    ? 'Променете параметрите и настройките на страницата'
    : 'Създайте нова страница на сайта си'
);
?>

<?php Form::create([
    'action' => $action,
    'method' => 'POST',
    'files' => true,
]) ?>

<?php Form::section(
    title: 'Основни данни',
    slot: function () use ($page, $options) {
        Form::row(function () use ($page) {
            Form::input(
                'name',
                'Име на страницата',
                [
                    'value' => $page->name ?? '',
                    'required' => true,
                ]
            );

            Form::input(
                'slug',
                'URL Slug',
                [
                    'value' => $page->slug ?? '',
                ]
            );

            Form::date(
                'created_at',
                'Дата на публикуване',
                [
                    'value' => date(
                        'Y-m-d H:i',
                        strtotime($page->created_at ?? 'now')
                    ),
                ]
            );
        }, [
            'md' => 2,
            'lg' => 3,
        ]);

        Form::textarea(
            'excerpt',
            'Резюме',
            [
                'value' => $options['excerpt'] ?? '',
                'rows' => 5,
            ]
        );
    }
); ?>

<?php Form::section(
    title: 'Миниатюра',
    slot: function () use ($options) {
        ?>
    <div class="flex flex-wrap gap-5">
        <?php Form::image(
                'featured_image',
                'Декстоп',
                [
                    'current_image' => $options['featured_image'] ?? null,
                    'title' => 'Desktop',
                    'description' => '1024x1024px',
                ]
            ); ?>

        <?php Form::image(
                'tablet_image',
                'Таблет',
                [
                    'current_image' => $options['tablet_image'] ?? null,
                    'title' => 'Tablet',
                    'description' => '768x1024px',
                ]
            ); ?>

        <?php Form::image(
                'mobile_image',
                'Телефон',
                [
                    'current_image' => $options['mobile_image'] ?? null,
                    'title' => 'Mobile',
                    'description' => '400x800px',
                ]
            ); ?>
    </div>
    <?php
    }
); ?>

<?php Form::section(
    title: 'Настройки на страницата',
    slot: function () use ($page, $pages, $options) {
        Form::row(function () use ($page, $pages, $options) {
            $templates = [
                '' => 'Без шаблон',
            ] + PageTemplateDiscovery::getTemplates(ACTIVE_THEME);

            Form::customSelect(
                'page_template',
                'Шаблон на страницата',
                $templates,
                $options['page_template'] ?? ''
            );

            $parentOptions = [
                '' => 'Няма (главна страница)',
            ];

            foreach ($pages as $parentPage) {
                if (
                    isset($page->id) &&
                    (int) $parentPage->id === (int) $page->id
                ) {
                    continue;
                }

                $parentOptions[$parentPage->id] =
                    $parentPage->display_name;
            }

            Form::customSelect(
                'parent_id',
                'Родителска страница',
                $parentOptions,
                $page->parent_id ?? ''
            );
        }, [
            'md' => 2,
        ]);

        $withPageOptions = filter_var(
            $options['is_with_page_options'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        );
        ?>

    <div x-data='{
                withPageOptions: <?= json_encode($withPageOptions) ?>
            }'>
        <?php Form::row(function () use ($page, $options) {
                Form::toggle(
                    'is_active',
                    'Активна страница',
                    [
                        'value' => $page->is_active ?? true,
                        'description' => 'Ако деактивирате страницата, тя няма да бъде достъпна за посетителите на сайта.',
                    ]
                );

                Form::toggle(
                    'use_full_slug',
                    'Използване на пълния път (full_slug)',
                    [
                        'value' => filter_var(
                            $options['use_full_slug'] ?? true,
                            FILTER_VALIDATE_BOOLEAN
                        ),
                        'description' => 'Ако е включено, ще се генерира пълен път (напр. /parent/child). Ако е изключено - само slug (напр. /child).',
                    ]
                );

                if (!empty($page->id)) {
                    Form::toggle(
                        'is_with_page_options',
                        'Активиране на още полета',
                        [
                            'value' => filter_var(
                                $options['is_with_page_options'] ?? false,
                                FILTER_VALIDATE_BOOLEAN
                            ),
                            'description' => 'Ако е активирано, ще можете да добавите още полета за тази страница.',
                            'attr' => [
                                '@change' => 'withPageOptions = $event.target.checked',
                            ],
                        ]
                    );
                }
            }, [
                'md' => 2,
                'lg' => 3,
            ]); ?>

        <div x-show="withPageOptions" x-collapse x-cloak>
            <div class="mt-5">
                <?php Form::row(function () use ($options) {
                        Form::input(
                            'page_options_key',
                            'Ключ на допълнителните полета',
                            [
                                'value' => $options['page_options_key'] ?? '',
                                'description' => 'Въведете името на файла, който да се зареди (напр. "Home").',
                            ]
                        );
                    }, [
                        'md' => 2,
                    ]); ?>
            </div>
        </div>
    </div>
    <?php
    }
); ?>

<?php if (!empty($options['page_template'])): ?>
    <?php View::dispatchOptions($page, 'template'); ?>
<?php endif; ?>

<?php if ($page->getOption('is_with_page_options', false)): ?>
    <?php View::dispatchOptions($page, 'elements'); ?>
<?php endif; ?>

<?php Form::submit(
    $isEdit ? 'Запазване' : 'Създаване',
    'fa-save'
) ?>

<?php Form::close() ?>
