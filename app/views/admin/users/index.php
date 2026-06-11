<?php

use Flex\Core\UI\Form;
use Flex\Core\UI\Table;

$users = $users ?? [];
$roles = $roles ?? [];

$initialStatuses = [];
foreach ($users as $user) {
    if (isset($user->id)) {
        $initialStatuses[$user->id] = (bool) $user->is_active;
    }
}

$tableManagerConfig = [
    'toggleUrl' => '/admin/users/toggle',
    'deleteUrl' => '/admin/users/delete',
    'initialStatuses' => $initialStatuses,
    'confirmDeleteMessage' => 'Сигурни ли сте, че искате да изтриете този потребител?',
];
?>

<div x-data='tableManager(<?= json_encode($tableManagerConfig, JSON_UNESCAPED_UNICODE) ?>)'>
    <?php Table::header(slot: function () use ($roles) { ?>
        <?php Table::search('Търсене на потребител...'); ?>

        <?php 
        $roleOptions = ['' => 'Всички роли'];
        if (!empty($roles)) {
            foreach ($roles as $role) {
                $roleOptions[$role->slug] = $role->name;
            }
        }
        Form::customSelect('role', '', $roleOptions, $_GET['role'] ?? ''); 
        ?>

        <?php 
        $statusOptions = [
            '' => 'Всички статуси',
            'active' => 'Активни',
            'inactive' => 'Неактивни'
        ];
        Form::customSelect('status', '', $statusOptions, $_GET['status'] ?? ''); 
        ?>

        <?php Table::submit('Приложи'); ?>
        <?php Table::reset('/admin/users'); ?>
    <?php }); ?>

    <?php Table::create($users)
        ->column('Потребител', fn($user) => Table::textCell($user->fullname ?? null), 'fullname', 'left', fn($user) => '/admin/users/edit/' . $user->id)
        
        ->column('Имейл', fn($user) => Table::textCell($user->email ?? null), 'email')
        
        ->column('Роля', function ($user) {
            $userRoles = $user->roles;
            $roleName = $userRoles->isNotEmpty() ? $userRoles->first()->name : null;
            return Table::textCell($roleName, 'Няма');
        }, 'role')
        
        ->column('Статус', function ($user) {
            return Table::statusBadge('Активен|Неактивен', 'success', $user->id ?? null);
        }, 'is_active')
        
        ->column('Действия', function ($user) {
            if (!isset($user->id)) {
                return '';
            }

            return Table::actionsMenu(slot: function ($u) {
                ?>
                <?= Table::actionLink(
                    "/admin/users/edit/{$u->id}",
                    'Редактирай',
                    'fa-solid fa-user-pen'
                ) ?>

                <?php if ($u->id !== ($_SESSION['user_id'] ?? null)): ?>
                    
                    <?= Table::statusToggle($u->id, 'Деактивирай', 'Активирай') ?>

                    <?= Table::actionButton(
                        click: "deleteItem({$u->id})",
                        label: 'Изтрий',
                        icon: 'fa-solid fa-trash-can',
                        type: 'delete',
                        extraAttributes: ":disabled=\"typeof loading !== 'undefined' && loading[{$u->id}]\""
                    ) ?>

                <?php endif; ?>
                <?php
            }, item: $user);
        }, null, 'right')
        ->render('mt-5'); ?>
</div>
