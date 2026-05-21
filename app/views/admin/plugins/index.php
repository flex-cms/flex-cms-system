<?php
use Flex\Core\UI\Table;

$plugins = $plugins ?? [];

$initialStatuses = [];
foreach ($plugins as $plugin) {
    $initialStatuses[$plugin->id] = (bool) $plugin->is_active;
}
?>

<div x-data="pluginManager(<?= htmlspecialchars(json_encode($initialStatuses), ENT_QUOTES, 'UTF-8') ?>)">
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
        ob_start(); ?>
        <div class="flex items-center gap-3">
            <div class="p-2 bg-slate-100 dark:bg-slate-700 rounded-md text-slate-500">
                <i class="fa-solid fa-plug text-base"></i>
            </div>
            <div>
                <span class="font-medium text-slate-900 dark:text-white block"><?= htmlspecialchars($plugin->name) ?></span>
                <span class="text-xs text-slate-400 font-mono block">slug: <?= htmlspecialchars($plugin->slug) ?></span>
            </div>
        </div>
        <?php return ob_get_clean();
    }, 'name')

        ->column('Описание', function ($plugin) {
        ob_start(); ?>
        <div class="max-w-md">
            <p class="text-slate-600 dark:text-slate-300 line-clamp-2 mb-1">
                <?= htmlspecialchars($plugin->description) ?>
            </p>
            <span
                class="inline-flex items-center bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs px-2 py-0.5 rounded-full">
                v<?= htmlspecialchars($plugin->version) ?>
            </span>
        </div>
        <?php return ob_get_clean();
    })

        ->column('Статус', function ($plugin) {
        ob_start(); ?>
        <span x-text="statuses[<?= $plugin->id ?>] ? 'Активен' : 'Деактивиран'"
            :class="statuses[<?= $plugin->id ?>] ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400'"
            class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold">
        </span>
        <?php return ob_get_clean();
    }, 'is_active')

        ->column('Действие', function ($plugin) {
        ob_start(); ?>
        <div class="flex justify-end">
            <?= Table::actionsMenu([
                [
                    'html' => fn($p) => Table::actionButton(
                        "togglePlugin({$p->id})",
                        'Деактивирай',
                        'fa-solid fa-power-off',
                        'text-red-500 dark:text-red-500 hover:bg-slate-100 dark:hover:bg-slate-700',
                        "x-show=\"statuses[{$p->id}]\" :disabled=\"loading[{$p->id}]\""
                    )
                ],
                [
                    'html' => fn($p) => Table::actionButton(
                        "togglePlugin({$p->id})",
                        'Активирай',
                        'fa-solid fa-play',
                        'text-green-500 dark:text-green-500 hover:bg-slate-100 dark:hover:bg-slate-700',
                        "x-show=\"!statuses[{$p->id}]\" :disabled=\"loading[{$p->id}]\""
                    )
                ],
                [
                    'html' => fn($p) => Table::actionButton(
                        "deletePlugin({$p->id})",
                        'Премахни',
                        'fa-solid fa-trash-can',
                        'text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 font-medium border-t border-slate-100 dark:border-slate-700/50 mt-1 pt-2',
                        "x-show=\"!statuses[{$p->id}]\" :disabled=\"loading[{$p->id}]\""
                    )
                ]
            ], $plugin); ?>
        </div>
        <?php return ob_get_clean();
    }, null, 'right')
        ->render('mt-5'); ?>
</div>
