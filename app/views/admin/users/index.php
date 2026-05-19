<?php

use Flex\Core\UI\Table;

$users = $users ?? [];
$roles = $roles ?? [];
?>

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

    <?php Table::submit('Приложи'); ?>
    <?php Table::reset('/admin/users'); ?>
<?php }); ?>

<?php
Table::create($users)
    ->column('Потребител', fn($user) => $user->username ?? '---', 'username')
    ->column('Имейл', fn($user) => $user->email ?? '---', 'email')
    ->column('Роля', function ($user) {
        $roles = $user->roles;

        return ($roles->isNotEmpty())
            ? $roles->first()->name
            : '<span class="text-slate-400">Няма</span>';
    }, 'role')
    ->render('mt-5');
