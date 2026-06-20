<?php

use Flex\Core\UI\Form;
use Flex\Core\UI\Table;

$roles = $roles ?? [];

$initialStatuses = [];
foreach ($roles as $role) {
    if (isset($role->id)) {
        $initialStatuses[$role->id] = (bool) $role->is_active;
    }
}

$statusOptions = ['' => 'Всички статуси', 'active' => 'Активни', 'inactive' => 'Неактивни', 'deleted' => 'В кошчето'];

$tableManagerConfig = [
    'toggleUrl' => '/admin/users/roles/toggle',
    'initialStatuses' => $initialStatuses,
    'deleteUrl' => '/admin/users/roles/delete',
    'restoreUrl' => '/admin/users/roles/restore',
    'forceDeleteUrl' => '/admin/users/roles/force-delete',
    'confirmDeleteMessage' => 'Сигурни ли сте, че искате да изтриете тази роля?',
    'confirmRestoreMessage' => 'Сигурни ли сте, че искате да възстановите тази роля?',
    'confirmForceDeleteMessage' => 'ВНИМАНИЕ: Това действие е перманентно! Сигурни ли сте?',
];
?>

<div x-data='tableManager(<?= json_encode($tableManagerConfig, JSON_UNESCAPED_UNICODE) ?>)'>
    <?php Table::header(slot: function () use ($statusOptions) { ?>
        <?php Table::search('Търсене на роля...'); ?>

        <?php Form::customSelect('status', '', $statusOptions, $_GET['status'] ?? ''); ?>

        <?php Table::submit('Приложи'); ?>
        <?php Table::reset('/admin/users/roles'); ?>
    <?php }); ?>

    <?php Table::create($roles)
        ->column('Име на роля', function ($role) {
            return Table::avatarWithLabel(
                avatarUrl: $role->avatar_url ?? null,
                label: $role->name ?? '---',
                color: $role->color ?? '#6366f1',
                size: 36,
                isDefault: !empty($role->is_default)
            );
        }, 'name', 'left', fn($role) => '/admin/users/roles/edit/' . $role->id)

        ->column('Slug', function ($role) {
            return Table::statusBadge($role->slug ?? '', 'code');
        }, 'slug')

        ->column('Описание', function ($role) {
            return Table::textCell($role->description ?? null);
        })

        ->column('Статус', function ($role) {
            return Table::statusBadge('Активна|Неактивна', 'success', $role->id ?? null);
        }, 'is_active')

        ->column('Действия', function ($role) {
            if (!isset($role->id)) return '';

            return Table::actionsMenu(slot: function ($item) {
                $isDeleted = !empty($item->deleted_at);
                ?>
                
                <?php if (!$isDeleted): ?>
                    <?= Table::actionLink(
                        "/admin/users/roles/edit/{$item->id}",
                        'Редактиране',
                        'fa-solid fa-pen-to-square'
                    ) ?>

                    <?php if ($item->slug !== 'admin'): ?>
                        <?= Table::statusToggle($item->id, 'Деактивирай', 'Активирай') ?>
                        
                        <?= Table::actionButton(
                            click: "deleteItem({$item->id})",
                            label: 'Изтриване',
                            icon: 'fa-solid fa-trash-can',
                            type: 'delete',
                            extraAttributes: ":disabled=\"typeof loading !== 'undefined' && loading[{$item->id}]\""
                        ) ?>
                    <?php endif; ?>
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
            }, item: $role);
        }, null, 'right')
    ->render('mt-5'); ?>
</div>
