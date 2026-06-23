<?php

use Flex\Core\Helpers\DateHelper;
use Flex\Core\UI\Form;
use Flex\Core\UI\Table;

$pages = $pages ?? [];

$initialStatuses = [];
foreach ($pages as $page) {
    if (isset($page->id)) {
        $initialStatuses[$page->id] = (bool) $page->is_active;
    }
}

$tableManagerConfig = [
    'toggleUrl' => '/admin/pages/toggle',
    'initialStatuses' => $initialStatuses,
    'deleteUrl' => '/admin/pages/delete',
    'restoreUrl' => '/admin/pages/restore',
    'forceDeleteUrl' => '/admin/pages/force-delete',
    'confirmDeleteMessage' => 'Сигурни ли сте, че искате да изтриете тази страница?',
    'confirmRestoreMessage' => 'Сигурни ли сте, че искате да възстановите тази страница?',
    'confirmForceDeleteMessage' => 'ВНИМАНИЕ: Това действие е перманентно! Сигурни ли сте?',
];
$statusOptions = [
    '' => 'По подразбиране',
    'active' => 'Активни',
    'inactive' => 'Неактивни',
    'deleted' => 'В кошчето'
];
?>

<div x-data='tableManager(<?= json_encode($tableManagerConfig, JSON_UNESCAPED_UNICODE) ?>)'>
    <?php Table::header(slot: function () use ($statusOptions) { ?>
        <?php Table::search('Търсене на страница...'); ?>

        <?php Form::customSelect('status', '', $statusOptions, $_GET['status'] ?? ''); ?>

        <?php Table::submit('Приложи'); ?>
        <?php Table::reset('/admin/pages'); ?>
    <?php }); ?>

    <?php Table::create($pages)
        ->column('Страница', function ($page) {
            return $page->display_name . ' <br /> ' . '<span class="text-xs dark:text-white text-black">' . $page->full_slug . '</span>';
        }, 'name', 'left', fn($page) => '/admin/pages/edit/' . $page->id, '_self', true)

        ->column('Създадена', fn($page) => DateHelper::format($page->created_at, true), 'created_at')

        ->column('Статус', function ($page) {
            return Table::statusBadge('Активирана|Деактивирана', 'success', $page->id ?? null);
        }, 'is_active')

        ->column('Действия', function ($p) {
            if (!isset($p->id)) return '';

            return Table::actionsMenu(slot: function ($t) {
                $isDeleted = !empty($t->deleted_at);
                ?>
                
                <?php if (!$isDeleted): ?>
                    <?= Table::actionLink(
                        "/admin/pages/edit/{$t->id}",
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
    ->render('mt-5'); ?>
</div>