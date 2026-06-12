<?php

use Flex\Core\UI\Form;
use Flex\Core\UI\Table;

$templates = $templates ?? [];
$categories = $categories ?? [];

$categoryOptions = ['' => 'Всички категории'];
foreach ($categories as $cat) {
    $categoryOptions[$cat] = $cat;
}

$tableManagerConfig = [
    'deleteUrl' => '/admin/email-templates/delete',
    'confirmDeleteMessage' => 'Сигурни ли сте, че искате да изтриете този шаблон?',
];
?>

<div x-data='tableManager(<?= json_encode($tableManagerConfig, JSON_UNESCAPED_UNICODE) ?>)'>
    <?php Table::header(slot: function () use ($categoryOptions) { ?>
        <?php Table::search('Търсене на шаблон...'); ?>
        
        <?php 
        Form::customSelect('category', '', $categoryOptions, $_GET['category'] ?? ''); 
        ?>

        <?php Table::submit('Приложи'); ?>
        <?php Table::reset('/admin/email-templates'); ?>
    <?php }); ?>

    <?php Table::create($templates)
    ->column('Име на шаблон', function ($t) {
        return Table::textCell($t->name ?? '---');
    }, 'name', 'left', fn($t) => '/admin/email-templates/edit/' . $t->id)

    ->column('Slug', function ($t) {
        return Table::statusBadge($t->slug ?? '', 'code');
    }, 'slug')

    ->column('Категория', function ($t) {
        return Table::textCell($t->category ?? '---');
    }, 'category')

    ->column('Действия', function ($t) {
        if (!isset($t->id)) {
            return '';
        }

        return Table::actionsMenu(slot: function ($item) {
            ?>
            <?= Table::actionLink(
                "/admin/email-templates/edit/{$item->id}",
                'Редактирай',
                'fa-solid fa-pen-to-square'
            ) ?>

            <?= Table::actionButton(
                click: "deleteItem({$item->id})",
                label: 'Изтрий',
                icon: 'fa-solid fa-trash-can',
                type: 'delete',
                extraAttributes: ":disabled=\"typeof loading !== 'undefined' && loading[{$item->id}]\""
            ) ?>
            <?php
        }, item: $t);
    }, null, 'right')
    ->render('mt-5'); ?>
</div>
