<?php

use Flex\Core\Helpers\DateHelper;
use Flex\Core\UI\Table;

$pages = $pages ?? [];
?>

<div x-data="tableManager({
    deleteUrl: '/admin/pages/delete',
    confirmDeleteMessage: 'Сигурни ли сте, че искате да изтриете тази страница?'
})">
    <?php Table::header(slot: function () { ?>
        <?php Table::search('Търсене на страница...'); ?>
        <?php Table::submit('Приложи'); ?>
        <?php Table::reset('/admin/pages'); ?>
    <?php }); ?>

    <?php Table::create($pages)
        ->column('Име на страница', function ($page) {
            return '<div class="font-medium text-slate-900 dark:text-white">' . htmlspecialchars($page->name) . '</div>';
        }, 'name')

        ->column('Slug', function ($page) {
            return Table::statusBadge('/' . $page->slug, 'code');
        }, 'slug')

        ->column('Създадена', function ($page) {
            return '<span class="text-sm text-slate-500">' . DateHelper::format($page->created_at, true) . '</span>';
        })

        ->column('Действия', function ($page) {
            if (!isset($page->id)) return '';
            
            ob_start(); ?>
            <div class="flex justify-end">
                <?= Table::actionsMenu(slot: function ($p) {
                    ob_start(); ?>

                    <?= Table::actionLink(
                        "/admin/pages/edit/{$p->id}",
                        'Редактирай',
                        'fa-solid fa-pen-to-square'
                    ) ?>

                    <?= Table::actionButton(
                        click: "deleteItem({$p->id})",
                        label: 'Изтрий',
                        icon: 'fa-solid fa-trash-can',
                        type: 'delete',
                        extraAttributes: ":disabled=\"loading[{$p->id}]\""
                    ) ?>

                    <?php return ob_get_clean();
                }, item: $page); ?>
            </div>
            <?php return ob_get_clean();
        }, null, 'right')
        ->render('mt-5'); ?>
</div>