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

<div x-data="pluginManager({
    toggleUrl: '/admin/plugins/toggle',
    deleteUrl: '/admin/plugins/delete',
    updateUrl: '/admin/plugins/update', 
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
        ->column('Име', function ($plugin) {
            return Table::avatarWithLabel(
                avatarUrl: null,
                label: $plugin->name ?? '---',
                color: '#6366f1',
                size: 36,
                isDefault: false
            );
        }, 'name')

        ->column('Версия', function ($plugin) {
            $versionText = 'v' . ($plugin->version ?? '1.0.0');
            return Table::statusBadge(text: $versionText, type: 'code');
        })

        ->column('Описание', function ($plugin) {
            return Table::textCell($plugin->description ?? null);
        })

        ->column('Автор', function ($plugin) {
            if (empty($plugin->author)) {
                return '<span class="text-slate-400 dark:text-slate-600">—</span>';
            }

            if (!empty($plugin->author_url)) {
                return '<a href="' . htmlspecialchars($plugin->author_url, ENT_QUOTES, 'UTF-8') . '" 
                           target="_blank" 
                           rel="noopener noreferrer" 
                           class="text-indigo-500 hover:text-indigo-600 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium underline decoration-indigo-500/20 hover:decoration-indigo-500 transition-all">' 
                           . htmlspecialchars($plugin->author, ENT_QUOTES, 'UTF-8') . 
                       '</a>';
            }

            return '<span class="text-slate-700 dark:text-slate-300 font-medium">' . htmlspecialchars($plugin->author, ENT_QUOTES, 'UTF-8') . '</span>';
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
                        click: "updatePlugin({$p->id})",
                        label: 'Обновяване',
                        icon: 'fa-solid fa-cloud-arrow-down',
                        type: 'primary',
                        extraAttributes: ":disabled=\"loading[{$p->id}]\""
                    ) ?>

                    <?= Table::actionButton(
                        click: "toggleStatus({$p->id})",
                        label: 'Деактивиране',
                        icon: 'fa-solid fa-power-off',
                        type: 'danger',
                        extraAttributes: "x-show=\"statuses[{$p->id}]\" :disabled=\"loading[{$p->id}]\""
                    ) ?>

                    <?= Table::actionButton(
                        click: "toggleStatus({$p->id})",
                        label: 'Активиране',
                        icon: 'fa-solid fa-play',
                        type: 'success',
                        extraAttributes: "x-show=\"!statuses[{$p->id}]\" :disabled=\"loading[{$p->id}]\""
                    ) ?>

                    <?= Table::actionButton(
                        click: "deleteItem({$p->id})",
                        label: 'Премахване',
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