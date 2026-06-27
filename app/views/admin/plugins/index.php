<?php

use Flex\Core\Helpers\DateHelper;
use Flex\Core\UI\Form;
use Flex\Core\UI\Table;

$plugins = $plugins ?? [];

$initialStatuses = [];
foreach ($plugins as $plugin) {
    if (isset($plugin->id)) {
        $initialStatuses[$plugin->id] = (bool) $plugin->is_active;
    }
}

$pluginManagerConfig = [
    'toggleUrl' => '/admin/plugins/toggle',
    'deleteUrl' => '/admin/plugins/delete',
    'updateUrl' => '/admin/plugins/update',
    'initialStatuses' => $initialStatuses,
    'confirmDeleteMessage' => 'Сигурни ли сте, че искате да премахнете този плъгин?',
];
?>

<div x-data='pluginManager(<?= json_encode($pluginManagerConfig, JSON_UNESCAPED_UNICODE) ?>)'>
    <?php Table::header(slot: function () { ?>
        <?php Table::search('Търсене на плъгин...'); ?>

        <?php $statusOptions = [
            '' => 'Всички статуси',
            'active' => 'Активни',
            'inactive' => 'Неактивни'
        ];

        Form::customSelect('status', '', $statusOptions, $_GET['status'] ?? ''); ?>

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

    ->column('Slug', function ($plugin) {
        return $plugin->slug;
    })

    ->column('Версия', function ($plugin) {
        $versionText = 'v' . ($plugin->version ?? '1.0.0');
        return Table::statusBadge(text: $versionText, type: 'code');
    })

    ->column('Описание', function ($plugin) {
        return Table::textCell($plugin->description ?? null);
    })

    ->column(
        'Инсталирано',
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

        return Table::actionsMenu(slot: function ($p) {
            ?>
            <?= Table::actionButton(
                click: "updatePlugin({$p->id})",
                label: 'Обновяване',
                icon: 'fa-solid fa-cloud-arrow-down',
                type: 'primary',
                extraAttributes: ":disabled=\"loading[{$p->id}]\""
            ) ?>

            <?= Table::statusToggle($p->id) ?>

            <?= Table::actionButton(
                click: "deleteItem({$p->id})",
                label: 'Премахване',
                icon: 'fa-solid fa-trash-can',
                type: 'delete',
                extraAttributes: ":disabled=\"loading[{$p->id}]\""
            ) ?>
            <?php
        }, item: $plugin);

    }, null, 'right')
    ->render('mt-5'); ?>
</div>
