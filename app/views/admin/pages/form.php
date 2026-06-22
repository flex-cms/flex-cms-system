<?php

use Flex\Core\PageTemplates\PageTemplateDiscovery;
use Flex\Core\Routing\View;
use Flex\Core\UI\Form;

$isEdit = isset($page->id);
$action = $isEdit ? "/admin/pages/update/{$page->id}" : "/admin/pages/store";
?>

<?php Form::create(['action' => $action, 'method' => 'POST', 'files' => true]) ?>

<?php Form::heading('Основна информация'); ?>

<?php Form::section(title: 'Основни данни', slot: function () use ($page) { ?>

    <div class="grid md:grid-cols-2 2xl:grid-cols-3 gap-5">
        <?php Form::file('featured_image', 'Предно изображение', [
            'current_image' => $page->options['featured_image'] ?? null,
            'title' => 'Desktop',
            'description' => '1024x1024px'
        ]); ?>

        <?php Form::file('tablet_image', 'Таблет изображение', [
            'current_image' => $page->options['tablet_image'] ?? null,
            'title' => 'Tablet',
            'description' => '768x1024px'
        ]); ?>

        <?php Form::file('mobile_image', 'Мобилно изображение', [
            'current_image' => $page->options['mobile_image'] ?? null,
            'title' => 'Mobile',
            'description' => '400x800px'
        ]); ?>
    </div>

    <?php Form::row(function () use ($page) { ?>

        <?php Form::input(
            'name',
            'Име на страницата',
            [
                'value' => $page->name ?? '',
                'required' => true
            ]
        ); ?>

        <?php Form::input('slug', 'URL Slug', [
            'value' => $page->slug ?? ''
        ]); ?>

    <?php }, 1, 2, 3); ?>

    <?php Form::textarea('excerpt', 'Резюме', [
        'value' => $page->options['excerpt'] ?? '',
        'rows' => 5
    ]); ?>

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

        Form::date('created_at', 'Дата на публикуване', [
            'value' => date('Y-m-d H:i', strtotime($page->created_at ?? 'now'))
        ]);

    }, 1, 2, 3); ?>

    <?php Form::row(function () use ($page) { ?>

        <?php Form::toggle('is_active', 'Активна страница', [
            'value' => $page->is_active ?? true,
            'description' => 'Ако е изключено, страницата няма да бъде достъпна за потребителите.'
        ]); ?>

        <?php Form::toggle('use_full_slug', 'Използване на пълния път (full_slug)', [
            'value' => ($page->options['slug_display_mode'] ?? 'full') === 'full',
            'description' => 'Ако е включено, ще се генерира пълен път (напр. /parent/child). Ако е изключено - само slug (напр. /child).'
        ]); ?>

    <?php }, 1, 2, 3); ?>

<?php }); ?>

<?php if (!empty($page->options['page_template']))
    View::renderPageTemplate($page); ?>

<?php Form::submit(!$isEdit ? 'Създаване' : 'Запазване', 'fa-save') ?>

<?php Form::close() ?>