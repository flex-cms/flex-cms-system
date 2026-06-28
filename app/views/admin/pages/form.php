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

        <?php Form::row(function () use ($page) { ?>

            <?php Form::toggle('is_active', 'Активна страница', [
                'value' => $page->is_active ?? true,
                'description' => 'Ако е изключено, страницата няма да бъде достъпна за потребителите.'
            ]); ?>

            <?php Form::toggle('use_full_slug', 'Използване на пълния път (full_slug)', [
                'value' => ($page->options['use_full_slug'] ?? true),
                'description' => 'Ако е включено, ще се генерира пълен път (напр. /parent/child). Ако е изключено - само slug (напр. /child).'
            ]); ?>

        <?php }, ['md' => 2, 'lg' => 3]); ?>

    <?php }); ?>

    <?php if (!empty($page->options['page_template']))
        View::renderPageTemplate($page); ?>

    <?php Form::submit(!$isEdit ? 'Създаване' : 'Запазване', 'fa-save') ?>

<?php Form::close() ?>