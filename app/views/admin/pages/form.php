<?php
use Flex\Core\UI\Form;

$isEdit = isset($page->id);
$action = $isEdit ? "/admin/pages/update/{$page->id}" : "/admin/pages/store";
?>

<?php Form::create(['action' => $action, 'method' => 'POST', 'files' => true]) ?>

<?php Form::section(title: 'Основни данни', slot: function () use ($page) { ?>
    <?php Form::row(function () use ($page) {
        Form::input('name', 'Име на страницата', ['value' => $page->name ?? '']);
        Form::input('slug', 'URL Slug', ['value' => $page->slug ?? '']);
    }); ?>

    <?php Form::textarea('excerpt', 'Резюме', [
        'value' => $page->options['excerpt'] ?? '',
        'rows' => 5
    ]); ?>
<?php }); ?>

<?php Form::section(title: 'Изображения', slot: function () use ($page) { ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
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
<?php }); ?>

<?php Form::submit(!$isEdit ? 'Създаване' : 'Запазване', 'fa-save') ?>

<?php Form::close() ?>
