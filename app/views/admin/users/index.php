<?php

use Flex\Core\UI\Table;

$users = $users ?? [];
$roles = $roles ?? [];

$initialStatuses = [];
foreach ($users as $user) {
    if (isset($user->id)) {
        $initialStatuses[$user->id] = (bool) $user->is_active;
    }
}
?>

<div x-data="tableManager({
    deleteUrl: '/admin/users/delete',
    toggleUrl: '/admin/users/toggle',
    initialStatuses: <?= htmlspecialchars(json_encode($initialStatuses), ENT_QUOTES, 'UTF-8') ?>,
    confirmDeleteMessage: 'Сигурни ли сте, че искате да изтриете този потребител?'
})">
    <?php Table::header(slot: function () use ($roles) { ?>
        <?php Table::search('Търсене на потребител...'); ?>

        <?php $roleOptions = ['' => 'Всички роли'];

        if (!empty($roles)) {
            foreach ($roles as $role) {
                $roleOptions[$role->slug] = $role->name;
            }
        }
        Table::select('role', $roleOptions); ?>

        <?php $statusOptions = [
            '' => 'Всички статуси',
            'active' => 'Активни',
            'inactive' => 'Неактивни'
        ];
        Table::select('status', $statusOptions); ?>

        <?php Table::submit('Приложи'); ?>
        <?php Table::reset('/admin/users'); ?>
    <?php }); ?>

    <?php Table::create($users)
        ->column('Потребител', fn($user) => Table::textCell($user->fullname ?? null), 'fullname')

        ->column('Имейл', fn($user) => Table::textCell($user->email ?? null), 'email')

        ->column('Роля', function ($user) {
            $userRoles = $user->roles;
            $roleName = $userRoles->isNotEmpty() ? $userRoles->first()->name : null;

            return Table::textCell($roleName, 'Няма');
        }, 'role')

        ->column('Статус', function ($user) {
            return Table::statusBadge('Активен', 'success', $user->id ?? null);
        }, 'is_active')

        ->column('Действие', function ($user) {
            if (!isset($user->id))
                return '';
            ob_start(); ?>
            <div class="flex justify-end">
                <?= Table::actionsMenu(slot: function ($u) {
                    ob_start(); ?>

                    <?= Table::actionLink(
                        "/admin/users/edit/{$u->id}",
                        'Редактирай',
                        'fa-solid fa-user-pen'
                    ) ?>

                    <?php if ($u->id !== ($_SESSION['user_id'] ?? null)): ?>

                        <?= Table::actionButton(
                            click: "toggleStatus({$u->id})",
                            label: 'Деактивирай',
                            icon: 'fa-solid fa-power-off',
                            type: 'danger',
                            extraAttributes: "x-show=\"typeof statuses !== 'undefined' && statuses[{$u->id}]\" :disabled=\"typeof loading !== 'undefined' && loading[{$u->id}]\""
                        ) ?>

                        <?= Table::actionButton(
                            click: "toggleStatus({$u->id})",
                            label: 'Активирай',
                            icon: 'fa-solid fa-play',
                            type: 'success',
                            extraAttributes: "x-show=\"typeof statuses !== 'undefined' && !statuses[{$u->id}]\" :disabled=\"typeof loading !== 'undefined' && loading[{$u->id}]\""
                        ) ?>

                        <?= Table::actionButton(
                            click: "deleteItem({$u->id})",
                            label: 'Изтрий',
                            icon: 'fa-solid fa-trash-can',
                            type: 'delete',
                            extraAttributes: ":disabled=\"typeof loading !== 'undefined' && loading[{$u->id}]\""
                        ) ?>

                    <?php endif; ?>

                    <?php return ob_get_clean();
                }, item: $user) ?>
            </div>
        <?php return ob_get_clean();
    }, null, 'right')
        ->render('mt-5'); ?>
</div>
