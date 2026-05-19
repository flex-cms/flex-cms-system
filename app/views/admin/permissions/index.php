<?php

use Flex\Core\UI\Table;

?>

<?php Table::header(slot: function () { ?>
    <?php Table::search('Търсене на разрешение...'); ?>
    <?php Table::submit('Приложи'); ?>
    <?php Table::reset('/admin/permissions'); ?>
<?php }); ?>

<?php
Table::create($permissions)
    ->column('Име на разрешение', fn($permission) => isset($permission->name) 
        ? "<span class='font-medium text-slate-900 dark:text-slate-100'>{$permission->name}</span>" 
        : '---')
    ->column('Slug', fn($permission) => isset($permission->slug) ? "<code class='bg-slate-100 dark:bg-slate-700 px-2 py-1 rounded text-xs'>{$permission->slug}</code>" : '---')
    ->column('Описание', fn($permission) => $permission->description ? $permission->description : '-')
    ->column('Действия', fn($permission) => isset($permission->id) ? "
            <div class='flex gap-3'>
                <a href='/admin/permissions/edit/{$permission->id}' class='text-indigo-500 hover:text-indigo-700'><i class='fa-solid fa-pen-to-square'></i></a>
                <button class='text-rose-500 hover:text-rose-700'><i class='fa-solid fa-trash'></i></button>
            </div>
        " : "")
    ->render('mt-5');
