<?php

use Flex\Core\UI\Table;

$roles = $roles ?? [];

$initialStatuses = [];
foreach ($roles as $role) {
    if (isset($role->id)) {
        $initialStatuses[$role->id] = (bool) $role->is_active;
    }
}
?>

<div x-data="tableManager({
    deleteUrl: '/admin/users/roles/delete',
    toggleUrl: '/admin/users/roles/toggle',
    confirmDeleteMessage: 'Сигурни ли сте, че искате да изтриете тази роля?',
    initialStatuses: <?= htmlspecialchars(json_encode($initialStatuses), ENT_QUOTES, 'UTF-8') ?>
})">
    <?php Table::header(slot: function () { ?>
        <?php Table::search('Търсене на роля...'); ?>

        <?php
        $statusOptions = [
            '' => 'Всички статуси',
            'active' => 'Активни',
            'inactive' => 'Неактивни'
        ];
        Table::select('status', $statusOptions);
        ?>

        <?php Table::submit('Приложи'); ?>
        <?php Table::reset('/admin/users/roles'); ?>
    <?php }); ?>

    <?php Table::create($roles)
        ->column('Име на роля', function ($role) {
        return Table::avatarWithLabel(
            avatarUrl: $role->avatar_url ?? null,
            label: $role->name ?? '---',
            color: $role->color ?? '#6366f1',
            size: 36,
            isDefault: !empty($role->is_default)
        );
    }, 'name')

        ->column('Slug', function ($role) {
        return Table::statusBadge($role->slug ?? '', 'code');
    }, 'slug')

        ->column('Описание', function ($role) {
        return Table::textCell($role->description ?? null);
    })

        ->column('Статус', function ($role) {
        return Table::statusBadge('Активна', 'success', $role->id ?? null);
    }, 'is_active')

        ->column('Действия', function ($role) {
        if (!isset($role->id))
            return '';
        ob_start(); ?>
        <div class="flex justify-end">
            <?= Table::actionsMenu(slot: function ($r) {
                ob_start(); ?>

                <?= Table::actionLink(
                    "/admin/users/roles/edit/{$r->id}",
                    'Редактирай',
                    'fa-solid fa-pen-to-square'
                ) ?>

                <?php if ($r->slug !== 'admin'): ?>

                    <?= Table::actionButton(
                        click: "toggleStatus({$r->id})",
                        label: 'Деактивирай',
                        icon: 'fa-solid fa-power-off',
                        type: 'danger',
                        extraAttributes: "x-show=\"statuses[{$r->id}]\" :disabled=\"loading[{$r->id}]\""
                    ) ?>

                    <?= Table::actionButton(
                        click: "toggleStatus({$r->id})",
                        label: 'Активирай',
                        icon: 'fa-solid fa-play',
                        type: 'success',
                        extraAttributes: "x-show=\"!statuses[{$r->id}]\" :disabled=\"loading[{$r->id}]\""
                    ) ?>

                    <?= Table::actionButton(
                        click: "deleteItem({$r->id})",
                        label: 'Изтрий',
                        icon: 'fa-solid fa-trash-can',
                        type: 'delete',
                        extraAttributes: ":disabled=\"loading[{$r->id}]\""
                    ) ?>

                <?php endif; ?>

                <?php return ob_get_clean();
            }, item: $role); ?>
        </div>
        <?php return ob_get_clean();
    }, null, 'right')
        ->render('mt-5'); ?>
</div>
