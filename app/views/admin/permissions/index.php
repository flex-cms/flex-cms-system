<?php

use Flex\Core\UI\Table;

$permissions = $permissions ?? [];

$modules = \Flex\Models\Permission::select('module')->distinct()->pluck('module')->toArray();
$moduleOptions = ['' => 'Всички модули'] + array_combine($modules, $modules);
?>

<div x-data="tableManager({
    deleteUrl: '/admin/users/permissions/delete',
    confirmDeleteMessage: 'Сигурни ли сте, че искате да изтриете това разрешение?'
})">
    <?php Table::header(slot: function () use ($moduleOptions) { ?>
        <?php Table::search('Търсене на разрешение...'); ?>

        <?php Table::select('module', $moduleOptions); ?>

        <?php Table::submit('Приложи'); ?>
        <?php Table::reset('/admin/users/permissions'); ?>
    <?php }); ?>

    <?php Table::create($permissions)
        ->column('Име', function ($p) {
            return '<span class="font-semibold text-slate-800 dark:text-white">' . ($p->name ?? '---') . '</span>';
        }, 'name')

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
            if (!isset($p->id))
                return '';
            ob_start(); ?>
        <div class="flex justify-end">
            <?= Table::actionsMenu(slot: function ($item) {
                ob_start(); ?>

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
                    extraAttributes: ":disabled=\"loading[{$item->id}]\""
                ) ?>

                <?php return ob_get_clean();
            }, item: $p); ?>
        </div>
        <?php return ob_get_clean();
        }, null, 'right')
        ->render('mt-5'); ?>
</div>