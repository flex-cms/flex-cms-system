<?php

use Flex\Core\UI\Form;
use Flex\Core\UI\Table;

$templates = $templates ?? [];
$categories = $categories ?? [];

$categoryOptions = ['' => 'Всички категории'];
foreach ($categories as $cat) {
    if (!empty($cat)) {
        $categoryOptions[$cat] = $cat;
    }
}

$initialStatuses = [];
foreach ($templates as $template) {
    if (isset($template->id)) {
        $initialStatuses[$template->id] = (bool) $template->is_active;
    }
}

$statusOptions = [
    '' => 'По подразбиране',
    'active' => 'Активни',
    'inactive' => 'Неактивни',
    'deleted' => 'В кошчето'
];

$tableManagerConfig = [
    'toggleUrl' => '/admin/email-templates/toggle',
    'initialStatuses' => $initialStatuses,
    'deleteUrl' => '/admin/email-templates/delete',
    'restoreUrl' => '/admin/email-templates/restore',
    'forceDeleteUrl' => '/admin/email-templates/force-delete',
    'confirmDeleteMessage' => 'Сигурни ли сте, че искате да изтриете този имейл шаблон?',
    'confirmRestoreMessage' => 'Сигурни ли сте, че искате да възстановите този имейл шаблон?',
    'confirmForceDeleteMessage' => 'ВНИМАНИЕ: Това действие е перманентно! Сигурни ли сте?',
];
?>

<div x-data='tableManager(<?= json_encode($tableManagerConfig, JSON_UNESCAPED_UNICODE) ?>)'>
    <?php Table::header(slot: function () use ($categoryOptions, $statusOptions) { ?>
        <?php Table::search('Търсене на шаблон...'); ?>
        
        <?php Form::customSelect('category', '', $categoryOptions, $_GET['category'] ?? ''); ?>
        <?php Form::customSelect('status', '', $statusOptions, $_GET['status'] ?? ''); ?>

        <?php Table::submit('Приложи'); ?>
        <?php Table::reset('/admin/email-templates'); ?>
    <?php }); ?>

    <?php Table::create($templates)
    ->column('Име на шаблон', function ($t) {
        return Table::textCell($t->name ?? '---');
    }, 'name', 'left', fn($t) => '/admin/email-templates/edit/' . $t->id)

    ->column('Slug', function ($t) {
        return Table::statusBadge($t->slug ?? '', 'code');
    }, 'slug')

    ->column('Категория', function ($t) {
        return Table::textCell($t->category ?? '---');
    }, 'category')

    ->column('Статус', function ($t) {
        return Table::statusBadge('Активен|Неактивен', 'success', $t->id ?? null);
    }, 'is_active')

    ->column('Действия', function ($p) {
        if (!isset($p->id)) return '';

        return Table::actionsMenu(slot: function ($t) {
            $isDeleted = !empty($t->deleted_at);
            ?>
            
            <?php if (!$isDeleted): ?>
                <?= Table::actionLink(
                    "/admin/email-templates/edit/{$t->id}",
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