<?php

use Flex\Core\UI\Table;

$plugins = $plugins ?? [];

ob_start();
?>
{
<?php foreach ($plugins as $plugin): ?>
    <?= $plugin->id ?>: <?= $plugin->is_active ? 'true' : 'false' ?>,
<?php endforeach; ?>
}
<?php
$initialStatuses = ob_get_clean();
?>

<?php Table::header(slot: function () { ?>
    <?php Table::search('Търсене на плъгин...'); ?>

    <?php
    $statusOptions = [
        '' => 'Всички статуси',
        'active' => 'Само активни',
        'inactive' => 'Само деактивирани'
    ];
    Table::select('status', $statusOptions, $_GET['status'] ?? '');
    ?>

    <?php Table::submit('Приложи'); ?>
    <?php Table::reset('/admin/plugins'); ?>
<?php }); ?>

<div x-data="pluginManager(<?= trim($initialStatuses) ?>)">
    <?php
    Table::create($plugins)

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
            <p class="text-slate-600 dark:text-slate-300 line-clamp-2 mb-1"><?= htmlspecialchars($plugin->description) ?>
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
        <div class="flex items-center">
            <span
                class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold <?= $plugin->is_active ? 'bg-emerald-100 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400' ?>"
                :class="statuses[<?= $plugin->id ?>] ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-400'"
                x-text="statuses[<?= $plugin->id ?>] ? 'Активен' : 'Деактивиран'">
                <?= $plugin->is_active ? 'Активен' : 'Деактивиран' ?>
            </span>
        </div>
        <?php return ob_get_clean();
        }, 'is_active')

        ->column('Действие', function ($plugin) {
            ob_start(); ?>
        <div class="flex justify-end items-center gap-2">

            <button type="button" x-show="statuses[<?= $plugin->id ?>]" @click="togglePlugin(<?= $plugin->id ?>)"
                :disabled="loading[<?= $plugin->id ?>]"
                class="inline-flex items-center px-3 py-1.5 bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/30 dark:hover:bg-amber-900/40 text-amber-700 dark:text-amber-400 text-xs font-medium rounded-md border border-amber-200 dark:border-amber-900/50 transition-colors"
                title="Деактивиране на плъгин">
                <i class="fa-solid fa-power-off mr-1.5"></i> Деактивирай
            </button>

            <button type="button" x-show="!statuses[<?= $plugin->id ?>]" @click="togglePlugin(<?= $plugin->id ?>)"
                :disabled="loading[<?= $plugin->id ?>]"
                class="inline-flex items-center px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/30 dark:hover:bg-indigo-900/40 text-indigo-700 dark:text-indigo-400 text-xs font-medium rounded-md border border-indigo-200 dark:border-indigo-900/50 transition-colors"
                title="Активиране на плъгин">
                <i class="fa-solid fa-play mr-1.5"></i> Активирай
            </button>

            <button type="button" x-show="!statuses[<?= $plugin->id ?>]" @click="deletePlugin(<?= $plugin->id ?>)"
                :disabled="loading[<?= $plugin->id ?>]"
                class="inline-flex items-center justify-center p-2 bg-red-50 hover:bg-red-100 dark:bg-red-950/30 dark:hover:bg-red-900/40 text-red-600 dark:text-red-400 rounded-md transition-colors border border-red-100 dark:border-red-900/50"
                title="Изтриване от базата и физическо премахване на папката">
                <i class="fa-solid fa-trash-can text-sm"></i>
            </button>

        </div>
        <?php return ob_get_clean();
        })

        ->render('mt-5');
    ?>
</div>
