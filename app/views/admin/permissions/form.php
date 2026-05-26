<?php

use Flex\Core\UI\Components\Alert;
use Flex\Core\UI\Form;
use Flex\Core\UI\Page;

$permission = $permission ?? null;
$assignedRoleIds = $assignedRoleIds ?? [];
$allRoles = $allRoles ?? [];

Page::header(
    title: $permission ? 'Редактиране на разрешение' : 'Създаване на ново разрешение',
    backUrl: '/admin/users/permissions',
    subtitle: 'Дефинирайте техническите детайли и логическото групиране на това разрешение'
);
?>

<form action="<?= $permission ? '/admin/users/permissions/update/' . $permission->id : '/admin/users/permissions/store' ?>" method="POST" class="max-w-3xl">

    <?php Form::section(function () use ($permission) { ?>

        <div class="space-y-5">
            <?php Form::input('name', 'Име на разрешението', [
                'value' => $permission?->name,
                'placeholder' => 'напр. edit_users',
                'required' => true
            ]); ?>

            <?php Form::input('slug', 'Slug на разрешението', [
                'value' => $permission?->slug,
                'placeholder' => 'напр. edit_users',
                'required' => true
            ]); ?>

            <?php if ($permission): ?>
                <?php
                echo Alert::make(
                    'Внимание: Промяната на „Slug“-а на разрешението може да счупи логическите проверки в кода (напр. $user->can(\'...\')). Бъдете внимателни!'
                )->warning()->render();
                ?>
            <?php endif; ?>

            <?php Form::input('module', 'Модул (група)', [
                'value' => $permission?->module,
                'placeholder' => 'напр. Users',
                'required' => true
            ]); ?>

            <?php Form::textarea('description', 'Описание', [
                'value' => $permission?->description,
                'placeholder' => 'Опишете какво позволява това разрешение...',
                'rows' => 4
            ]); ?>
        </div>

    <?php }, 'Основни данни'); ?>

    <?php Form::section(function () use ($allRoles, $assignedRoleIds) { ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <?php foreach ($allRoles as $role): ?>
                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-lg">
                    <?php Form::toggle('roles[' . $role->id . ']', $role->name, [
                        'value' => in_array($role->id, $assignedRoleIds ?? []),
                        'description' => "Дай права за '{$role->name}'"
                    ]); ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php }, 'Роли с достъп до това разрешение'); ?>

    <?php Form::submit($permission ? 'Запазване на промените' : 'Създаване на разрешение', 'fa-save'); ?>

</form>
