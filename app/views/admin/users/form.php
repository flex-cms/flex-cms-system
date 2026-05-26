<?php

use Flex\Core\UI\Form;

$user = $user ?? null;
$allRoles = $allRoles ?? [];
$assignedRoleIds = $assignedRoleIds ?? [];
?>

<form action="<?= $user ? '/admin/users/update/' . $user->id : '/admin/users/create' ?>" method="POST"
    class="max-w-5xl">
    <?php Form::section(function () use ($user) { ?>

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
                'disabled' => true,
                'type' => 'email'
            ]); ?>
        <?php }); ?>

    <?php }, 'Основни данни за потребителя'); ?>

    <?php Form::section(function () use ($user) { ?>
        <?php Form::input('password', 'Парола', [
            'value' => '',
            'placeholder' => $user ? 'Оставете празно, за да запазите текущата' : 'Въведете парола',
            'type' => 'password'
        ]); ?>
    <?php }, 'Сигурност'); ?>

    <?php Form::section(function () use ($user) { ?>
        <?php Form::toggle('is_active', 'Активна роля', [
            'value' => $user?->is_active ?? true,
            'description' => 'Ако деактивирате ролята, потребителите с тази роля няма да могат да използват свързаните с нея права.'
        ]); ?>
    <?php }, 'Статус на потребителя'); ?>

    <?php Form::section(function () use ($allRoles, $assignedRoleIds) { ?>
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
    <?php }, 'Роли на потребителя'); ?>

    <?php Form::submit($user ? 'Запазване' : 'Създаване', 'fa-save'); ?>
</form>
