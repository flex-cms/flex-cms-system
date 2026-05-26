<?php

use Flex\Core\UI\Components\Alert;
use Flex\Core\UI\Form;
use Flex\Core\UI\Page;

$permission = $permission ?? null;

Page::header(
    title: $permission ? 'Редактиране на разрешение' : 'Създаване на ново разрешение',
    backUrl: '/admin/users/permissions',
    subtitle: 'Дефинирайте техническите детайли и логическото групиране на това разрешение'
);
?>

<form action="<?= $permission ? '/admin/users/permissions/update/' . $permission->id : '/admin/users/permissions/store' ?>"
    method="POST" class="max-w-3xl">

    <?php Form::section(function () use ($permission) { ?>
        <?php Form::input('name', 'Име на разрешението', [
            'value' => $permission?->name,
            'placeholder' => 'напр. edit_users',
            'required' => true
        ]); ?>

        <?php Form::row(function () use ($permission) { ?>
            <?php Form::input('slug', 'Slug на разрешението', [
                'value' => $permission?->slug,
                'placeholder' => 'напр. edit_users',
                'required' => true
            ]); ?>

            <?php Form::input('module', 'Модул (група)', [
                'value' => $permission?->module,
                'placeholder' => 'напр. Users',
                'required' => true
            ]); ?>
        <?php }); ?>

        <?php Form::textarea('description', 'Описание', [
            'value' => $permission?->description,
            'placeholder' => 'Опишете какво позволява това разрешение...',
            'rows' => 4
        ]); ?>

        <?php if ($permission): ?>
            <?php
            echo Alert::make(
                'Внимание: Промяната на „Slug“-а на разрешението може да счупи логическите проверки в кода (напр. $user->can(\'...\')). Бъдете внимателни!'
            )->warning()->render();
            ?>
        <?php endif; ?>

    <?php }, 'Основни данни'); ?>

    <div class="mt-6">
        <?php Form::submit($permission ? 'Запазване на промените' : 'Създаване на разрешение', 'fa-save'); ?>
    </div>

</form>