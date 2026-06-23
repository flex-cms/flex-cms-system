<?php

use Flex\Core\Routing\View;
use Flex\Core\UI\Form;

$user = $user ?? null;
$allRoles = $allRoles ?? [];
$assignedRoleIds = $assignedRoleIds ?? [];

$isEdit = isset($user->id);
$action = $isEdit ? '/admin/users/update/' . $user->id : '/admin/users/create';
?>

<?php Form::create(['action' => $action, 'method' => 'POST', 'class' => 'max-w-5xl']) ?>

    <?php Form::heading('Основна информация'); ?>

    <?php Form::section(title: 'Основни данни за потребителя', slot: function () use ($user) { ?>

        <?php Form::row(function () use ($user) { ?>
        
            <?php Form::input('fullname', 'Пълно име', [
                'value' => $user?->fullname,
                'placeholder' => 'Иван Иванов',
                'required' => true
            ]); ?>

            <?php Form::input('email', 'Имейл адрес', [
                'value' => $user?->email,
                'placeholder' => 'ivan@example.com',
                'required' => true,
                'readonly' => true,
                'type' => 'email'
            ]); ?>

        <?php }, ['md' => 2]); ?>

        <?php Form::toggle('is_active', 'Активен потребител', [
            'value' => $user?->is_active ?? true,
            'description' => 'Ако деактивирате потребителя, той няма да има достъп до системата.'
        ]); ?>

    <?php }); ?>

    <?php Form::section(title: 'Сигурност', slot: function () use ($user) { ?>
        <?php View::component('password-strength', ['user' => $user], 'admin/users/components'); ?>
    <?php }); ?>

    <?php Form::section(title: 'Роли на потребителя', slot: function () use ($allRoles, $assignedRoleIds) { ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($allRoles as $role): ?>
                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-lg">
                    <?php Form::toggle('roles[' . $role->id . ']', $role->name, [
                        'value' => in_array($role->id, $assignedRoleIds ?? []),
                        'description' => $role->description ?? ''
                    ]); ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php }); ?>

    <?php Form::submit($user ? 'Запазване' : 'Създаване', 'fa-save'); ?>

<?php Form::close(); ?>