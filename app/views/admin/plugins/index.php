<?php
use Flex\Core\UI\Table;

$plugins = $plugins ?? [];

$initialStatuses = [];
foreach ($plugins as $plugin) {
    if (isset($plugin->id)) {
        $initialStatuses[$plugin->id] = (bool) $plugin->is_active;
    }
}
?>

<div x-data="tableManager({
    toggleUrl: '/admin/plugins/toggle',
    deleteUrl: '/admin/plugins/delete',
    initialStatuses: <?= htmlspecialchars(json_encode($initialStatuses), ENT_QUOTES, 'UTF-8') ?>,
    confirmDeleteMessage: 'Сигурни ли сте, че искате да премахнете този плъгин?'
})">
    <?php Table::header(slot: function () { ?>
        <?php Table::search('Търсене на плъгин...'); ?>

        <?php
        $statusOptions = [
            '' => 'Всички статуси',
            'active' => 'Активни',
            'inactive' => 'Неактивни'
        ];
        Table::select('status', $statusOptions);
        ?>

        <?php Table::submit('Приложи'); ?>
        <?php Table::reset('/admin/plugins'); ?>
    <?php }); ?>

    <?php Table::create($plugins)
        ->column('Име и Версия', function ($plugin) {
        return Table::avatarWithLabel(
            avatarUrl: null,
            label: $plugin->name ?? '---',
            color: '#6366f1',
            size: 36,
            isDefault: false
        );
    }, 'name')

        ->column('Описание', function ($plugin) {
        return Table::textCell($plugin->description ?? null);
    })

        ->column('Статус', function ($plugin) {
        return Table::statusBadge('Активен|Деактивиран', 'success', $plugin->id ?? null);
    }, 'is_active')

        ->column('Действие', function ($plugin) {
        if (!isset($plugin->id))
            return '';
        ob_start(); ?>
        <div class="flex justify-end">
            <?= Table::actionsMenu(slot: function ($p) {
                ob_start(); ?>

                <?= Table::actionButton(
                    click: "toggleStatus({$p->id})",
                    label: 'Деактивирай',
                    icon: 'fa-solid fa-power-off',
                    type: 'danger',
                    extraAttributes: "x-show=\"statuses[{$p->id}]\" :disabled=\"loading[{$p->id}]\""
                ) ?>

                <?= Table::actionButton(
                    click: "toggleStatus({$p->id})",
                    label: 'Активирай',
                    icon: 'fa-solid fa-play',
                    type: 'success',
                    extraAttributes: "x-show=\"!statuses[{$p->id}]\" :disabled=\"loading[{$p->id}]\""
                ) ?>

                <?= Table::actionButton(
                    click: "deleteItem({$p->id})",
                    label: 'Премахни',
                    icon: 'fa-solid fa-trash-can',
                    type: 'delete',
                    extraAttributes: "x-show=\"!statuses[{$p->id}]\" :disabled=\"loading[{$p->id}]\""
                ) ?>

                <?php return ob_get_clean();
            }, item: $plugin) ?>
        </div>
        <?php return ob_get_clean();
    }, null, 'right')
        ->render('mt-5'); ?>
</div>
