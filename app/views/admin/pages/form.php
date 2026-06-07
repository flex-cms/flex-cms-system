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
    <?php Form::row(function () use ($page) { ?>

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

    <?php }, 3); ?>

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

    <?php }); ?>

    <?php Form::textarea('excerpt', 'Резюме', [
        'value' => $page->options['excerpt'] ?? '',
        'rows' => 5
    ]); ?>

<?php }); ?>

<?php Form::section(title: 'Настройки на страницата', slot: function () use ($page) { ?>
    <?php
        $templates = [
            '' => 'Без шаблон',
        ] + PageTemplateDiscovery::getTemplates(ACTIVE_THEME);
        
        Form::customSelect(
            'page_template', 
            'Шаблон на страницата', 
            $templates, 
            $page->options['page_template'] ?? 'default'
        );
    ?>

    <?php Form::row(function () use ($page) { ?>

        <?php Form::toggle('is_active', 'Активна страница', [
            'value' => $page->is_active ?? true,
            'description' => 'Ако е изключено, страницата няма да бъде достъпна за потребителите.'
        ]); ?>

        <?php Form::date('created_at', 'Дата на публикуване', [
            'value' => date('Y-m-d H:i', strtotime($page->created_at ?? 'now'))
        ]); ?>

    <?php }); ?>
<?php }); ?>

<?php if (!empty($page->options['page_template'])) View::renderPageTemplate($page); ?>

<?php Form::submit(!$isEdit ? 'Създаване' : 'Запазване', 'fa-save') ?>

<?php Form::close() ?>