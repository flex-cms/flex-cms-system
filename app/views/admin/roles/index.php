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

$tableManagerConfig = [
    'toggleUrl' => '/admin/users/roles/toggle',
    'deleteUrl' => '/admin/users/roles/delete',
    'initialStatuses' => $initialStatuses,
    'confirmDeleteMessage' => 'Сигурни ли сте, че искате да изтриете тази роля?',
];
?>

<div x-data='tableManager(<?= json_encode($tableManagerConfig, JSON_UNESCAPED_UNICODE) ?>)'>
    <?php Table::header(slot: function () { ?>
        <?php Table::search('Търсене на роля...'); ?>

        <?php 
        $statusOptions = [
            '' => 'Всички статуси',
            'active' => 'Активни',
            'inactive' => 'Неактивни'
        ];
        Form::customSelect('status', '', $statusOptions, $_GET['status'] ?? ''); 
        ?>

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
            if (!isset($role->id)) {
                return '';
            }

            return Table::actionsMenu(slot: function ($r) {
                ?>
                <?= Table::actionLink(
                    "/admin/users/roles/edit/{$r->id}",
                    'Редактирай',
                    'fa-solid fa-pen-to-square'
                ) ?>

                <?php if ($r->slug !== 'admin'): ?>
                    
                    <?= Table::statusToggle($r->id, 'Деактивирай', 'Активирай') ?>

                    <?= Table::actionButton(
                        click: "deleteItem({$r->id})",
                        label: 'Изтрий',
                        icon: 'fa-solid fa-trash-can',
                        type: 'delete',
                        extraAttributes: ":disabled=\"typeof loading !== 'undefined' && loading[{$r->id}]\""
                    ) ?>

                <?php endif; ?>
                <?php
            }, item: $role);
        }, null, 'right')
    ->render('mt-5'); ?>
</div>
