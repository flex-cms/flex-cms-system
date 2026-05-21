<?php

use Flex\Core\UI\Table;

$users = $users ?? [];
$roles = $roles ?? [];
?>

<div x-data="deleteManager('/admin/users/delete')">
<?php Table::header(slot: function () use ($roles) { ?>
    <?php Table::search('Търсене на потребител...'); ?>

    <?php
    $roleOptions = ['' => 'Всички роли'];

    if (!empty($roles)) {
        foreach ($roles as $role) {
            $roleOptions[$role->slug] = $role->name;
        }
    }
    Table::select('role', $roleOptions);
    ?>

    <?php
    $statusOptions = [
        '' => 'Всички статуси',
        'active' => 'Активни',
        'inactive' => 'Неактивни'
    ];
    Table::select('status', $statusOptions);
    ?>

    <?php Table::submit('Приложи'); ?>
    <?php Table::reset('/admin/users'); ?>
<?php }); ?>

<?php Table::create($users)
    ->column('Потребител', fn($user) => $user->username ?? '---', 'username')
    ->column('Имейл', fn($user) => $user->email ?? '---', 'email')
    ->column('Роля', function ($user) {
    $roles = $user->roles;

    return ($roles->isNotEmpty())
        ? $roles->first()->name
        : '<span class="text-slate-400">Няма</span>';
}, 'role')
    ->column('Действие', function ($user) {
            ob_start(); ?>
            <div class="flex justify-end">
                <?= Table::actionsMenu([
                    [
                        'html' => fn($u) => Table::actionLink(
                            "/admin/users/{$u->id}/edit", 
                            'Редактирай', 
                            'fa-solid fa-user-pen'
                        )
                    ],
                    [
                        'visible' => fn($u) => $u->id !== ($_SESSION['user_id'] ?? null),
                        'html' => function ($u) {
                            ob_start(); ?>
                            <button type="button" 
                                @click="deleteItem(<?= $u->id ?>)"
                                :disabled="loading[<?= $u->id ?>]"
                                class="flex w-full items-center px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-md transition-colors font-medium border-t border-slate-100 dark:border-slate-700/50 mt-1 pt-2 disabled:opacity-50">
                                <i class="fa-solid fa-trash-can mr-2 w-4 text-center"></i> Изтрий
                            </button>
                            <?php return ob_get_clean();
                        }
                    ]
                ], $user); ?>
            </div>
            <?php return ob_get_clean();
        }, null, 'right')
    ->render('mt-5'); ?>
</div>