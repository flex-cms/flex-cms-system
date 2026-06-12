<?php

use Flex\Core\UI\Form;
use Flex\Core\UI\Table;

$permissions = $permissions ?? [];

$modules = \Flex\Models\Permission::select('module')->distinct()->pluck('module')->toArray();
$moduleOptions = ['' => 'Всички модули'] + array_combine($modules, $modules);

$tableManagerConfig = [
    'deleteUrl' => '/admin/users/permissions/delete',
    'confirmDeleteMessage' => 'Сигурни ли сте, че искате да изтриете това разрешение?',
];
?>

<div x-data='tableManager(<?= json_encode($tableManagerConfig, JSON_UNESCAPED_UNICODE) ?>)'>
    <?php Table::header(slot: function () use ($moduleOptions) { ?>
        <?php Table::search('Търсене на разрешение...'); ?>

        <?php 
        Form::customSelect('module', '', $moduleOptions, $_GET['module'] ?? ''); 
        ?>

        <?php Table::submit('Приложи'); ?>
        <?php Table::reset('/admin/users/permissions'); ?>
    <?php }); ?>

    <?php Table::create($permissions)
    ->column('Име', function ($p) {
        return '<span class="font-semibold text-slate-800 dark:text-white">' . ($p->name ?? '---') . '</span>';
    }, 'name', 'left', fn($p) => '/admin/users/permissions/edit/' . $p->id)

    ->column('Slug', function ($p) {
        return Table::statusBadge($p->slug ?? '', 'code');
    }, 'slug')

    ->column('Модул', function ($p) {
        return Table::statusBadge($p->module ?? 'General', 'default');
    }, 'module')

    ->column('Описание', function ($p) {
        return Table::textCell($p->description ?? null);
    })

    ->column('Действия', function ($p) {
        if (!isset($p->id)) {
            return '';
        }

        return Table::actionsMenu(slot: function ($item) {
            ?>
            <?= Table::actionLink(
                "/admin/users/permissions/edit/{$item->id}",
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
        }, item: $p);
    }, null, 'right')
    ->render('mt-5'); ?>
</div>