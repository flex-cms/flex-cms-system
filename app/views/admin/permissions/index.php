<?php

use Flex\Core\UI\Form;
use Flex\Core\UI\Table;
use Flex\Models\Permission;

$permissions = $permissions ?? [];

$initialStatuses = [];
foreach ($permissions as $permission) {
    if (isset($permission->id)) {
        $initialStatuses[$permission->id] = (bool) $permission->is_active;
    }
}

$modules = Permission::select('module')->distinct()->pluck('module')->toArray();
$moduleOptions = ['' => 'Всички модули'] + array_combine($modules, $modules);
$statusOptions = ['' => 'Всички статуси', 'active' => 'Активни', 'inactive' => 'Неактивни', 'deleted' => 'В кошчето'];

$tableManagerConfig = [
    'toggleUrl' => '/admin/users/permissions/toggle',
    'initialStatuses' => $initialStatuses,
    'deleteUrl' => '/admin/users/permissions/delete',
    'restoreUrl' => '/admin/users/permissions/restore',
    'forceDeleteUrl' => '/admin/users/permissions/force-delete',
    'confirmDeleteMessage' => 'Сигурни ли сте, че искате да изтриете това разрешение?',
    'confirmRestoreMessage' => 'Сигурни ли сте, че искате да възстановите това разрешение?',
    'confirmForceDeleteMessage' => 'ВНИМАНИЕ: Това действие е перманентно! Сигурни ли сте?',
];
?>

<div x-data='tableManager(<?= json_encode($tableManagerConfig, JSON_UNESCAPED_UNICODE) ?>)'>
    <?php Table::header(slot: function () use ($moduleOptions, $statusOptions) { ?>
        <?php Table::search('Търсене на разрешение...'); ?>

        <?php Form::customSelect('module', '', $moduleOptions, $_GET['module'] ?? ''); ?>
        <?php Form::customSelect('status', '', $statusOptions, $_GET['status'] ?? ''); ?>

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

    ->column('Статус', function ($role) {
        return Table::statusBadge('Активно|Неактивно', 'success', $role->id ?? null);
    }, 'is_active')

    ->column('Действия', function ($p) {
        if (!isset($p->id)) return '';

        return Table::actionsMenu(slot: function ($item) {
            $isDeleted = !empty($item->deleted_at);
            ?>
            
            <?php if (!$isDeleted): ?>
                <?= Table::actionLink(
                    "/admin/users/permissions/edit/{$item->id}",
                    'Редактиране',
                    'fa-solid fa-pen-to-square'
                ) ?>

                <?= Table::statusToggle($item->id, 'Деактивирай', 'Активирай') ?>

                <?= Table::actionButton(
                    click: "deleteItem({$item->id})",
                    label: 'Изтриване',
                    icon: 'fa-solid fa-trash-can',
                    type: 'delete',
                    extraAttributes: ":disabled=\"typeof loading !== 'undefined' && loading[{$item->id}]\""
                ) ?>
            <?php else: ?>
                <?= Table::actionButton(
                    click: "restoreItem({$item->id})",
                    label: 'Възстановяване',
                    icon: 'fa-solid fa-trash-arrow-up',
                    extraAttributes: ":disabled=\"typeof loading !== 'undefined' && loading[{$item->id}]\""
                ) ?>
                
                <?= Table::actionButton(
                    click: "forceDeleteItem({$item->id})",
                    label: 'Изтриване завинаги',
                    icon: 'fa-solid fa-skull',
                    type: 'delete',
                    extraAttributes: ":disabled=\"typeof loading !== 'undefined' && loading[{$item->id}]\""
                ) ?>
            <?php endif; ?>
            
            <?php
        }, item: $p);
    }, null, 'right')
    ->render('mt-5'); ?>
</div>
