<?php

use Flex\Core\PageTemplates\PageTemplateDiscovery;
use Flex\Core\Routing\View;
use Flex\Core\UI\Form;
use Flex\Core\UI\Page;

$isEdit = isset($page->id);
$action = $isEdit ? "/admin/pages/update/{$page->id}" : "/admin/pages/store";

Page::header(
    title: $page ? 'Редактиране на страница' : 'Създаване на страница',
    backUrl: '/admin/pages',
    subtitle: $page ? 'Променете параметрите и настройките на страницата' : 'Създайте нова страница на сайта си'
);
?>

<?php Form::create(['action' => $action, 'method' => 'POST', 'files' => true]) ?>

    <?php Form::heading('Основна информация'); ?>

    <?php Form::section(title: 'Основни данни', slot: function () use ($page) { ?>

        <?php Form::row(function () use ($page) {

            Form::input(
                'name',
                'Име на страницата',
                [
                    'value' => $page->name ?? '',
                    'required' => true
                ]
            );

            Form::input('slug', 'URL Slug', [
                'value' => $page->slug ?? ''
            ]);
            
            Form::date('created_at', 'Дата на публикуване', [
                'value' => date('Y-m-d H:i', strtotime($page->created_at ?? 'now'))
            ]);

        }, ['md' => 2, 'lg' => 3]);

        Form::textarea('excerpt', 'Резюме', [
            'value' => $page->options['excerpt'] ?? '',
            'rows' => 5
        ]);

    }); ?>

    <?php Form::section(title: 'Миниатюра', slot: function () use ($page) { ?>

        <div class="flex flex-wrap gap-5">
            
            <?php Form::image('featured_image', 'Декстоп', [
                'current_image' => $page->options['featured_image'] ?? null,
                'title' => 'Desktop',
                'description' => '1024x1024px'
            ]); ?>

            <?php Form::image('tablet_image', 'Таблет', [
                'current_image' => $page->options['tablet_image'] ?? null,
                'title' => 'Tablet',
                'description' => '768x1024px'
            ]); ?>

            <?php Form::image('mobile_image', 'Телефон', [
                'current_image' => $page->options['mobile_image'] ?? null,
                'title' => 'Mobile',
                'description' => '400x800px'
            ]); ?>

        </div>

    <?php }); ?>

    <?php Form::section(title: 'Настройки на страницата', slot: function () use ($page, $pages) { ?>

        <?php Form::row(function () use ($page, $pages) {

            $templates = [
                '' => 'Без шаблон',
            ] + PageTemplateDiscovery::getTemplates(ACTIVE_THEME);

            Form::customSelect(
                'page_template',
                'Шаблон на страницата',
                $templates,
                $page->options['page_template'] ?? 'default'
            );

            $pageOptions = ['' => 'Няма (главна страница)'];
            foreach ($pages as $p) {
                $pageOptions[$p->id] = $p->display_name;
            }
            Form::customSelect(
                'parent_id',
                'Родителска страница',
                $pageOptions,
                $page->parent_id ?? ''
            );

        }, ['md' => 2]); ?>

        <div x-data="{ withPageOptions: <?= $page->options['is_with_page_options'] ?? false ?> }">
            <?php Form::row(function () use ($page) {

                Form::toggle('is_active', 'Активна страница', [
                    'value' => $page->is_active ?? true,
                    'description' => 'Ако деактивирате страницата, тя няма да бъде достъпна за посетителите на сайта.'
                ]);

                Form::toggle('use_full_slug', 'Използване на пълния път (full_slug)', [
                    'value' => ($page->options['use_full_slug'] ?? true),
                    'description' => 'Ако е включено, ще се генерира пълен път (напр. /parent/child). Ако е изключено - само slug (напр. /child).'
                ]);

                if (!empty($page->id)) {
                    Form::toggle('is_with_page_options', 'Активиране на още полета', [
                        'value' => $page->options['is_with_page_options'] ?? false,
                        'description' => 'Ако е активирано, ще можете да добавите още полета за тази страница.',
                        'attr' => [
                            'name' => 'is_with_page_options',
                            '@change' => 'withPageOptions = $event.target.checked'
                        ]
                    ]);
                }

            }, ['md' => 2, 'lg' => 3]); ?>

            <div x-show="withPageOptions" x-collapse x-cloak>
                <div class="mt-5">
                    <?php Form::row(function () use ($page) {

                        Form::input('page_options_key', 'Ключ на допълнителните полета', [
                            'value' => $page->options['page_options_key'] ?? '',
                            'description' => 'Въведете името на файла, който да се зареди (напр. "Home").'
                        ]);

                    }, ['md' => 2]); ?>
                </div>
            </div>
        </div>

    <?php }); ?>

    <?php if (!empty($page->options['page_template']))
        View::dispatchOptions($page, 'template'); ?>

    <?php if (!empty($page->options['is_with_page_options']))
        View::dispatchOptions($page, 'options'); ?>

    <?php Form::submit(!$isEdit ? 'Създаване' : 'Запазване', 'fa-save') ?>

<?php Form::close() ?>