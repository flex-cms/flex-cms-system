<?php

use Flex\Core\UI\Table;

?>

<?php Table::header(slot: function () { ?>
    <?php Table::search('Търсене на роля...'); ?>
    <?php Table::submit('Приложи'); ?>
    <?php Table::reset('/admin/roles'); ?>
<?php }); ?>

<?php Table::create($roles)
    ->column('Име на роля', fn($role) => "
            <div class='flex items-center gap-3'>
                " . Table::avatar(
                $role->avatar_url ?? null,
                $role->name ?? '---',
                $role->color ?? '#6366f1',
                36
            ) . "
                <span class='font-medium text-slate-900 dark:text-slate-100'>" . ($role->name ?? '---') . "</span>
            </div>
        ")
    ->column('Slug', fn($role) => isset($role->slug) ? "<code class='bg-slate-100 dark:bg-slate-700 px-2 py-1 rounded text-xs'>{$role->slug}</code>" : '---')
    ->column('Описание', fn($role) => $role->description ? $role->description : '-')
    ->column('Действия', fn($role) => isset($role->id) ? "
            <div class='flex gap-3'>
                <a href='/admin/roles/edit/{$role->id}' class='text-indigo-500 hover:text-indigo-700'><i class='fa-solid fa-pen-to-square'></i></a>
                <button class='text-rose-500 hover:text-rose-700'><i class='fa-solid fa-trash'></i></button>
            </div>
        " : "")
    ->render('mt-5');
