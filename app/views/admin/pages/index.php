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
    'deleteUrl' => '/admin/pages/delete',
    'initialStatuses' => $initialStatuses,
    'confirmDeleteMessage' => 'Сигурни ли сте, че искате да изтриете тази страница?',
];
?>

<div x-data='tableManager(<?= json_encode($tableManagerConfig, JSON_UNESCAPED_UNICODE) ?>)'>
    <?php Table::header(slot: function () { ?>
        <?php Table::search('Търсене на страница...'); ?>

        <?php $statusOptions = [
            '' => 'По подразбиране',
            'active' => 'Активни',
            'inactive' => 'Неактивни',
            'deleted' => 'В кошчето'
        ];

        Form::customSelect('status', '', $statusOptions, $_GET['status'] ?? ''); ?>

        <?php Table::submit('Приложи'); ?>
        <?php Table::reset('/admin/pages'); ?>
    <?php }); ?>

    <?php Table::create($pages)
        ->column('Име на страница', fn($page) => $page->name, 'name', 'left', fn($page) => '/admin/pages/edit/' . $page->id)
        ->column('Slug', fn($page) => Table::statusBadge('/' . $page->slug, 'code'), 'slug', 'left', fn($page) => '/' . $page->slug, '__blank')
        ->column('Създадена', fn($page) => DateHelper::format($page->created_at, true), 'created_at')

        ->column('Статус', function ($page) {
        return Table::statusBadge('Активирана|Деактивирана', 'success', $page->id ?? null);
    }, 'is_active')

        ->column('Действия', function ($page) {
        if (!isset($page->id))
            return '';

        return Table::actionsMenu(slot: function ($p) {
            ?>
            <?= Table::actionLink(
                "/admin/pages/edit/{$p->id}",
                'Редактирай',
                'fa-solid fa-pen-to-square'
            ) ?>

            <?= Table::statusToggle($p->id) ?>

            <?= Table::actionButton(
                click: "deleteItem({$p->id})",
                label: 'Изтриване',
                icon: 'fa-solid fa-trash-can',
                type: 'delete',
                extraAttributes: ":disabled=\"loading[{$p->id}]\""
            ) ?>
            <?php
        }, item: $page);

    }, null, 'right')
        ->render('mt-5'); ?>
</div>