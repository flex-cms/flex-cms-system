<?php

use Flex\Core\Helpers\DateHelper;
use Flex\Core\UI\Form;
use Flex\Core\UI\Table;

$menus = $menus ?? [];

$initialStatuses = [];
foreach ($menus as $menu) {
    if (isset($menu->id)) {
        $initialStatuses[$menu->id] = (bool) $menu->is_active;
    }
}

$tableManagerConfig = [
    'toggleUrl' => '/admin/menus/toggle',
    'initialStatuses' => $initialStatuses,
    'deleteUrl' => '/admin/menus/delete',
    'restoreUrl' => '/admin/menus/restore',
    'forceDeleteUrl' => '/admin/menus/force-delete',
    'confirmDeleteMessage' => 'Сигурни ли сте, че искате да изтриете това меню?',
    'confirmRestoreMessage' => 'Сигурни ли сте, че искате да възстановите това меню?',
    'confirmForceDeleteMessage' => 'ВНИМАНИЕ: Действието е перманентно! Сигурни ли сте, че искате да изтриете това меню завинаги?',
];

$statusOptions = [
    '' => 'По подразбиране',
    'active' => 'Активни',
    'inactive' => 'Неактивни',
    'deleted' => 'В кошчето'
];
?>

<div
    x-data='tableManager(<?= json_encode($tableManagerConfig, JSON_UNESCAPED_UNICODE) ?>)'
    x-data-sortable='<?= json_encode($reorderConfig) ?>'
>
    <?php Table::header(slot: function () use ($statusOptions) { ?>
        <?php Table::search('Търсене на страница...'); ?>

        <?php Form::customSelect('status', '', $statusOptions, $_GET['status'] ?? ''); ?>

        <?php Table::submit('Приложи'); ?>
        <?php Table::reset('/admin/menus'); ?>
    <?php }); ?>

    <?php 
    Table::create($menus)
        ->column('Име', function ($m) {
            return '<span class="font-semibold text-slate-800 dark:text-white">' . ($m->name ?? '---') . '</span>';
        }, 'name', 'left', fn($m) => '/admin/menus/edit/' . $m->id)
        ->column('Синоним (Slug)', function($m) {
            return '<code class="text-xs bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded text-slate-600 dark:text-slate-400">' . htmlspecialchars($m->slug) . '</code>';
        })
        ->column('Статус', function($m) {
            return Table::statusBadge(
                text: $m->is_active ? 'Активно' : 'Неактивно', 
                type: $m->deleted_at ? 'neutral' : ($m->is_active ? 'success' : 'danger'), 
                reactiveId: $m->id
            );
        })
        ->column(
            'Създадена',
            fn($page) => sprintf(
                '<span
                    x-data="relativeTime(\'%s\')"
                    x-text="text"
                    title="%s"
                ></span>',
                DateHelper::iso($page->created_at),
                e(DateHelper::format($page->created_at, true))
            ),
            'created_at'
        )
        ->column('Действия', function ($p) {
            if (!isset($p->id)) return '';

            return Table::actionsMenu(slot: function ($t) {
                $isDeleted = !empty($t->deleted_at);
                ?>
                
                <?php if (!$isDeleted): ?>
                    <?= Table::actionLink(
                        "/admin/menus/edit/{$t->id}",
                        'Редактиране',
                        'fa-solid fa-pen-to-square'
                    ) ?>

                    <?= Table::statusToggle($t->id, 'Деактивиране', 'Активиране') ?>

                    <?= Table::actionButton(
                        click: "deleteItem({$t->id})",
                        label: 'Изтриване',
                        icon: 'fa-solid fa-trash-can',
                        type: 'delete',
                        extraAttributes: ":disabled=\"typeof loading !== 'undefined' && loading[{$t->id}]\""
                    ) ?>
                <?php else: ?>
                    <?= Table::actionButton(
                        click: "restoreItem({$t->id})",
                        label: 'Възстановяване',
                        icon: 'fa-solid fa-trash-arrow-up',
                        extraAttributes: ":disabled=\"typeof loading !== 'undefined' && loading[{$t->id}]\""
                    ) ?>
                    
                    <?= Table::actionButton(
                        click: "forceDeleteItem({$t->id})",
                        label: 'Изтриване завинаги',
                        icon: 'fa-solid fa-skull',
                        type: 'delete',
                        extraAttributes: ":disabled=\"typeof loading !== 'undefined' && loading[{$t->id}]\""
                    ) ?>
                <?php endif; ?>
                
                <?php
            }, item: $p);
        }, null, 'right')
        ->render('mt-5');
    ?>
</div>
