<?php

use Flex\Core\UI\Components\Alert;
use Flex\Core\UI\Form;
use Flex\Core\UI\Page;

$permission = $permission ?? null;
$assignedRoleIds = $assignedRoleIds ?? [];
$allRoles = $allRoles ?? [];

$isEdit = isset($permission->id);
$action = $isEdit ? '/admin/users/permissions/update/' . $permission->id : '/admin/users/permissions/store';

Page::header(
    title: $isEdit ? 'Редактиране на разрешение' : 'Създаване на ново разрешение',
    backUrl: '/admin/users/permissions',
    subtitle: 'Дефинирайте техническите детайли и логическото групиране на това разрешение'
);
?>

<?php Form::create(['action' => $action, 'method' => 'POST', 'class' => 'max-w-5xl']) ?>

    <?php Form::heading('Основна информация'); ?>

    <?php Form::section(title: 'Основни данни за разрешението', slot: function () use ($permission) { ?>

        <?php Form::row(function () use ($permission) { ?>

            <?php Form::input('name', 'Име на разрешението', [
                'value' => $permission?->name,
                'placeholder' => 'напр. Редактиране на потребители',
                'required' => true
            ]); ?>

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

        <?php }, ['md' => 2, 'lg' => 3]); ?>

        <?php if ($permission): ?>
            <div class="mt-4">
                <?php echo Alert::make('Внимание: Промяната на „Slug“-а може да счупи логическите проверки в кода (напр. $user->can(\'...\')). Бъдете внимателни!')->warning()->render(); ?>
            </div>
        <?php endif; ?>

        <?php Form::textarea('description', 'Описание', [
            'value' => $permission?->description,
            'placeholder' => 'Опишете какво позволява това разрешение...',
            'rows' => 4
        ]); ?>
    <?php }); ?>

    <?php Form::section(title: 'Роли с достъп до това разрешение', slot: function () use ($allRoles, $assignedRoleIds) { ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php foreach ($allRoles as $role): ?>
                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-lg">
                    <?php Form::toggle('roles[' . $role->id . ']', $role->name, [
                        'value' => in_array($role->id, $assignedRoleIds ?? []),
                        'description' => "Дай права за роля: " . ($role->description ?? $role->name)
                    ]); ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php }); ?>

    <?php Form::submit($isEdit ? 'Запазване на промените' : 'Създаване на разрешение', 'fa-save'); ?>

<?php Form::close(); ?>