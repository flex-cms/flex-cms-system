<?php

use Flex\Core\UI\Form;
use Flex\Core\UI\Table;

$plugins = $plugins ?? [];

$initialStatuses = [];
$initialInstalled = [];
$initialVersions = [];

foreach ($plugins as $plugin) {
    $slug = (string) $plugin->slug;

    $initialStatuses[$slug] = (bool) ($plugin->is_active ?? false);
    $initialInstalled[$slug] = (bool) ($plugin->is_installed ?? false);
    $initialVersions[$slug] = $plugin->version
        ? (string) $plugin->version
        : null;
}

$pluginManagerConfig = [
    'installUrl' => '/admin/plugins/install',
    'toggleUrl' => '/admin/plugins/toggle',
    'deleteUrl' => '/admin/plugins/delete',
    'updateUrl' => '/admin/plugins/update',

    'initialStatuses' => $initialStatuses,
    'initialInstalled' => $initialInstalled,
    'initialVersions' => $initialVersions,

    'confirmDeleteMessage' => 'Сигурни ли сте, че искате да премахнете този плъгин?',
];

$pluginToArray = static function ($plugin): array {
    $manifest = (array) ($plugin->manifest ?? []);
    $author = (array) ($manifest['author'] ?? []);

    return [
        'id' => (int) $plugin->id,
        'name' => (string) ($manifest['name'] ?? $plugin->name ?? ''),
        'slug' => (string) ($manifest['slug'] ?? $plugin->slug ?? ''),
        'description' => (string) ($manifest['description'] ?? $plugin->description ?? ''),
        'is_installed' => (bool) ($plugin->is_installed ?? false),
        'is_active' => (bool) ($plugin->is_active ?? false),
        'installed_version' => $plugin->version
            ? (string) $plugin->version
            : null,
        'available_version' => (string) (
            $manifest['version']
            ?? '1.0.0'
        ),
        'update_available' => $plugin->version !== null
            && version_compare(
                (string) ($manifest['version'] ?? '0.0.0'),
                (string) $plugin->version,
                '>'
            ),
        'type' => (string) ($manifest['type'] ?? 'plugin'),
        'license' => (string) ($manifest['license'] ?? ''),
        'homepage' => (string) ($manifest['homepage'] ?? ''),
        'repository' => (string) ($manifest['repository'] ?? ''),
        'provider' => (string) ($manifest['provider'] ?? ''),
        'author' => [
            'name' => (string) ($author['name'] ?? ''),
            'email' => (string) ($author['email'] ?? ''),
            'website' => (string) ($author['website'] ?? ''),
        ],
        'requires' => (array) ($manifest['requires'] ?? []),
        'routes' => (array) ($manifest['routes'] ?? []),
        'autoload' => (array) ($manifest['autoload'] ?? []),
        'features' => array_values((array) ($manifest['features'] ?? [])),
        'permissions' => array_values((array) ($manifest['permissions'] ?? [])),
        'boot' => (bool) ($manifest['boot'] ?? false),
        'admin_menu' => (bool) ($manifest['admin_menu'] ?? false),
        'migrations' => (bool) ($manifest['migrations'] ?? false),
        'seeders' => (bool) ($manifest['seeders'] ?? false),
        'assets' => $manifest['assets'] ?? false,
        'manifest_valid' => (bool) ($manifest['manifest_valid'] ?? false),
        'manifest_errors' => array_values(
            (array) ($manifest['manifest_errors'] ?? [])
        ),
        'manifest_warnings' => array_values(
            (array) ($manifest['manifest_warnings'] ?? [])
        ),
    ];
};
?>

<?php $deactivatedPlugins = $deactivatedPlugins ?? []; ?>

<?php if ($deactivatedPlugins !== []): ?>
    <div
        class="mb-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
        <div class="flex items-start gap-3">
            <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>

            <div>
                <p class="font-semibold">
                    Някои плъгини бяха деактивирани автоматично
                </p>

                <ul class="mt-2 space-y-1 text-sm">
                    <?php foreach ($deactivatedPlugins as $item): ?>
                        <li>
                            <strong><?= e($item['slug']) ?></strong>

                            <?php if (!empty($item['errors'])): ?>
                                — <?= e(implode(' ', $item['errors'])) ?>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
<?php endif; ?>

<div x-data='pluginManager(<?= htmlspecialchars(
    json_encode(
        $pluginManagerConfig,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    ),
    ENT_QUOTES,
    'UTF-8'
) ?>)' @keydown.escape.window="closeDetails()">
    <?php Table::header(slot: function () { ?>
        <?php Table::search('Търсене на плъгин...'); ?>

        <?php
        Form::customSelect(
            'status',
            '',
            [
                '' => 'Всички статуси',
                'active' => 'Активни',
                'inactive' => 'Неактивни',
            ],
            $_GET['status'] ?? ''
        );
        ?>

        <?php Table::submit('Приложи'); ?>
        <?php Table::reset('/admin/plugins'); ?>
    <?php }); ?>

    <?php if ($plugins->isEmpty()): ?>
        <div
            class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center dark:border-slate-700 dark:bg-slate-900">
            <div
                class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                <i class="fa-solid fa-puzzle-piece text-xl"></i>
            </div>

            <h2 class="mt-4 text-lg font-bold text-slate-900 dark:text-white">
                Няма намерени плъгини
            </h2>

            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                Добавете плъгин в директорията plugins или променете филтрите.
            </p>
        </div>
    <?php else: ?>
        <div class="mt-6 grid gap-5 md:grid-cols-2 xl:grid-cols-3">
            <?php foreach ($plugins as $plugin): ?>
                <?php
                $manifest = (array) ($plugin->manifest ?? []);
                $author = (array) ($manifest['author'] ?? []);
                $details = $pluginToArray($plugin);
                $detailsJson = htmlspecialchars(
                    json_encode(
                        $details,
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                    ),
                    ENT_QUOTES,
                    'UTF-8'
                );
                $slug = htmlspecialchars(
                    json_encode(
                        (string) $plugin->slug,
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                    ),
                    ENT_QUOTES,
                    'UTF-8'
                );
                $manifestValid = (bool) ($manifest['manifest_valid'] ?? false);
                ?>

                <article data-plugin-card="<?= (int) $plugin->id ?>"
                    class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-slate-700 dark:bg-slate-950">
                    <div class="flex flex-1 flex-col p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex min-w-0 items-center gap-3">
                                <div
                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-700">
                                    <i class="fa-solid fa-puzzle-piece text-lg"></i>
                                </div>

                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h2 class="truncate text-base font-bold text-slate-900 dark:text-white">
                                            <?= e($manifest['name'] ?? $plugin->name ?? 'Плъгин') ?>
                                        </h2>
                                        <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">
                                            <?= e($manifest['slug'] ?? $plugin->slug ?? '') ?>
                                        </p>

                                        <span
                                            class="rounded-md bg-slate-100 px-2 py-0.5 font-mono text-xs text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                            x-text="'Текуща версия: ' + (versions[<?= $slug ?>] || 'Не е инсталиран')"></span>
                                        <span
                                            class="rounded-md bg-slate-100 px-2 py-0.5 font-mono text-xs text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                            Налична:
                                            <?= e($manifest['version'] ?? 'Неизвестна') ?>
                                        </span>

                                        <?php if (!$manifestValid): ?>
                                            <span
                                                class="inline-flex items-center gap-1 rounded-md bg-red-50 px-2 py-1 text-xs font-semibold text-red-700 dark:bg-red-500/10 dark:text-red-400">
                                                <i class="fa-solid fa-triangle-exclamation"></i>
                                                Невалиден manifest
                                            </span>
                                        <?php elseif (!empty($manifest['manifest_warnings'])): ?>
                                            <span
                                                class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">
                                                <i class="fa-solid fa-circle-exclamation"></i>
                                                Има предупреждения
                                            </span>
                                        <?php else: ?>
                                            <span
                                                class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400">
                                                <i class="fa-solid fa-circle-check"></i>
                                                Валиден
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <span x-text="!installed[<?= $slug ?>]
                                    ? 'Не е инсталиран'
                                    : statuses[<?= $slug ?>]
                                        ? 'Активен'
                                        : 'Неактивен'"
                                :class="!installed[<?= $slug ?>]
                                    ? 'bg-slate-100 text-slate-600 ring-slate-500/20 dark:bg-slate-800 dark:text-slate-400'
                                    : statuses[<?= $slug ?>]
                                        ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400'
                                        : 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-400'"
                                class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset"></span>
                        </div>

                        <p class="mt-5 line-clamp-3 min-h-15 text-sm leading-6 text-slate-600 dark:text-slate-300">
                            <?= e($manifest['description'] ?? $plugin->description ?? 'Няма добавено описание за този плъгин.') ?>
                        </p>

                        <?php if (!empty($manifest['features'])): ?>
                            <div class="mt-4 flex flex-wrap gap-1">
                                <?php foreach (array_slice($manifest['features'], 0, 3) as $feature): ?>
                                    <span
                                        class="rounded bg-slate-100 dark:bg-slate-700 text-xs px-1.5 py-0.5">
                                        <?= e(ucwords(str_replace(['-', '_'], ' ', $feature))) ?>
                                    </span>
                                <?php endforeach; ?>

                                <?php if (count($manifest['features']) > 3): ?>
                                    <span
                                        class="rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                        +
                                        <?= count($manifest['features']) - 3 ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="mt-auto pt-5">
                            <div class="flex items-center justify-between border-t border-slate-100 pt-4 dark:border-slate-800">
                                <div class="min-w-0">
                                    <p class="text-xs text-slate-400">Автор</p>

                                    <?php if (!empty($author['website'])): ?>
                                        <a href="<?= e($author['website']) ?>" target="_blank" rel="noopener noreferrer"
                                            class="block truncate hover:underline text-sm">
                                            <?= e($author['name'] ?? 'KRISKATA.COM') ?>
                                            <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                        </a>
                                    <?php else: ?>
                                        <p class="truncate text-sm font-semibold text-slate-700 dark:text-slate-200">
                                            <?= e($author['name'] ?? $plugin->author ?? '—') ?>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <button type="button" @click='openDetails(<?= $detailsJson ?>)'
                                    class="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">
                                    Детайли
                                    <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex items-center gap-2 border-t border-slate-200 bg-slate-50 px-5 py-3 dark:border-slate-700 dark:bg-slate-800/50">
                        <button type="button" @click='handlePluginStatus(<?= $slug ?>)'
                            :disabled='loading[<?= $slug ?>] || (!installed[<?= $slug ?>] && !<?= $manifestValid ? 'true' : 'false' ?>)'
                            :class='buttonClass(
                    <?= $slug ?>)'
                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg px-3 py-2 text-sm
                    font-semibold transition disabled:cursor-not-allowed disabled:opacity-50"
                            >
                            <i class="fa-solid" :class='buttonIcon(<?= $slug ?>)'></i>

                            <span x-text='buttonText(<?= $slug ?>)'></span>
                        </button>

                        <button type="button" @click='updatePlugin(<?= $slug ?>)'
                            :disabled='loading[<?= $slug ?>] || !installed[<?= $slug ?>]' title="Обновяване"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-white text-primary shadow-sm ring-1 ring-slate-200 transition hover:bg-indigo-50 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-slate-900 dark:ring-slate-700 dark:hover:bg-slate-800">
                            <i class="fa-solid" :class="loading[<?= $slug ?>]
                ? 'fa-spinner fa-spin'
                : 'fa-cloud-arrow-down'"></i>
                        </button>

                        <button type="button" @click='deleteItem(<?= $slug ?>)'
                            :disabled='loading[<?= $slug ?>] || !installed[<?= $slug ?>]' title="Премахване"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-white text-red-600 shadow-sm ring-1 ring-slate-200 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50 dark:bg-slate-900 dark:text-red-400 dark:ring-slate-700 dark:hover:bg-slate-800">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <template x-teleport="body">
        <div x-show="detailsOpen" x-cloak class="fixed inset-0 z-100 flex items-center justify-center p-4 sm:p-6"
            role="dialog" aria-modal="true">
            <div x-show="detailsOpen" x-transition.opacity @click="closeDetails()"
                class="absolute inset-0 bg-slate-900/65 backdrop-blur-sm"></div>

            <div x-show="detailsOpen" x-transition:enter="transition duration-200 ease-out"
                x-transition:enter-start="translate-y-4 scale-95 opacity-0"
                x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                x-transition:leave="transition duration-150 ease-in"
                x-transition:leave-start="translate-y-0 scale-100 opacity-100"
                x-transition:leave-end="translate-y-4 scale-95 opacity-0" @click.stop
                class="relative flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-950">
                <template x-if="selectedPlugin">
                    <div class="flex min-h-0 flex-1 flex-col">
                        <div
                            class="flex items-start justify-between gap-4 border-b border-slate-200 px-6 py-5 dark:border-slate-700">
                            <div class="flex min-w-0 items-center gap-4">
                                <div
                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-700">
                                    <i class="fa-solid fa-puzzle-piece"></i>
                                </div>

                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h2
                                            x-text="selectedPlugin.name"
                                            class="truncate text-xl font-bold text-slate-900 dark:text-white"
                                        ></h2>

                                        <span
                                            x-text="selectedPlugin.installed_version
                                                ? 'Текуща версия: ' + selectedPlugin.installed_version
                                                : 'Не е инсталиран'"
                                            class="rounded-md bg-slate-100 px-2 py-1 font-mono text-xs text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                        ></span>

                                        <span
                                            x-text="'Налична: ' + (selectedPlugin.available_version || 'Неизвестна')"
                                            class="rounded-md bg-slate-100 px-2 py-1 font-mono text-xs text-slate-600 dark:bg-slate-800 dark:text-slate-300"
                                        ></span>
                                    </div>

                                    <p
                                        x-text="selectedPlugin.slug"
                                        class="mt-1 text-sm text-slate-500 dark:text-slate-400"
                                    ></p>
                                </div>
                            </div>

                            <button type="button" @click="closeDetails()"
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-white">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <div class="min-h-0 flex-1 overflow-y-auto px-6 py-6">
                            <p x-text="selectedPlugin.description || 'Няма добавено описание.'"
                                class="text-sm leading-6 text-slate-600 dark:text-slate-300"></p>

                            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                                <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                        Автор
                                    </p>

                                    <dl class="mt-2 space-y-2 text-sm">
                                        <div class="flex justify-between gap-4">
                                            <dt class="text-slate-500">Име</dt>

                                            <dd
                                                x-text="selectedPlugin.author.name || '—'"
                                                class="min-w-0 truncate text-right font-medium text-slate-900 dark:text-white"
                                            ></dd>
                                        </div>

                                        <div
                                            x-show="selectedPlugin.author.email"
                                            class="flex justify-between gap-4"
                                        >
                                            <dt class="text-slate-500">Имейл</dt>

                                            <dd class="min-w-0 text-right">
                                                <a
                                                    :href="'mailto:' + selectedPlugin.author.email"
                                                    x-text="selectedPlugin.author.email"
                                                    class="block truncate text-primary hover:underline"
                                                ></a>
                                            </dd>
                                        </div>

                                        <div
                                            x-show="selectedPlugin.author.website"
                                            class="flex justify-between gap-4"
                                        >
                                            <dt class="text-slate-500">Уебсайт</dt>

                                            <dd class="min-w-0 text-right">
                                                <a
                                                    :href="selectedPlugin.author.website"
                                                    x-text="selectedPlugin.author.website"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="block truncate text-primary hover:underline"
                                                ></a>
                                            </dd>
                                        </div>
                                    </dl>
                                </div>

                                <div class="rounded-xl border border-slate-200 p-4 dark:border-slate-700">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                        Съвместимост
                                    </p>

                                    <dl class="mt-2 space-y-2 text-sm">
                                        <div class="flex justify-between gap-4">
                                            <dt class="text-slate-500">PHP</dt>

                                            <dd
                                                x-text="selectedPlugin.requires.php || 'Не е зададено'"
                                                class="min-w-0 truncate text-right font-mono text-slate-900 dark:text-white"
                                            ></dd>
                                        </div>

                                        <div class="flex justify-between gap-4">
                                            <dt class="text-slate-500">Flex CMS</dt>

                                            <dd
                                                x-text="selectedPlugin.requires.flex || 'Не е зададено'"
                                                class="min-w-0 truncate text-right font-mono text-slate-900 dark:text-white"
                                            ></dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>

                            <div x-show="hasItems(selectedPlugin.features)" class="mt-6">
                                <h3 class="font-semibold text-slate-900 dark:text-white">
                                    Функционалности
                                </h3>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    <template x-for="feature in selectedPlugin.features" :key="feature">
                                        <span x-text="feature"
                                            class="rounded bg-slate-100 dark:bg-slate-700 px-2.5 py-1.5"></span>
                                    </template>
                                </div>
                            </div>

                            <div x-show="hasItems(selectedPlugin.permissions)" class="mt-6">
                                <h3 class="font-semibold text-slate-900 dark:text-white">
                                    Разрешения
                                </h3>

                                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                    <template x-for="permission in selectedPlugin.permissions" :key="permission">
                                        <div
                                            class="flex items-center gap-2 rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                                            <i class="fa-solid fa-shield-halved text-xs text-emerald-500"></i>
                                            <code x-text="permission"></code>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div x-show="hasItems(selectedPlugin.manifest_errors)"
                                class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-500/30 dark:bg-red-500/10">
                                <div class="flex items-start gap-3">
                                    <i class="fa-solid fa-circle-xmark mt-0.5 text-red-500"></i>

                                    <div>
                                        <h3 class="text-sm font-bold text-red-800 dark:text-red-300">
                                            Грешки в plugin.json
                                        </h3>

                                        <ul class="mt-2 space-y-1 text-sm text-red-700 dark:text-red-300">
                                            <template x-for="error in selectedPlugin.manifest_errors" :key="error">
                                                <li class="flex gap-2">
                                                    <span>•</span>
                                                    <span x-text="error"></span>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div x-show="hasItems(selectedPlugin.manifest_warnings)"
                                class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-500/30 dark:bg-amber-500/10">
                                <div class="flex items-start gap-3">
                                    <i class="fa-solid fa-triangle-exclamation mt-0.5 text-amber-500"></i>

                                    <div>
                                        <h3 class="text-sm font-bold text-amber-800 dark:text-amber-300">
                                            Предупреждения
                                        </h3>

                                        <ul class="mt-2 space-y-1 text-sm text-amber-700 dark:text-amber-300">
                                            <template x-for="warning in selectedPlugin.manifest_warnings"
                                                :key="warning">
                                                <li class="flex gap-2">
                                                    <span>•</span>
                                                    <span x-text="warning"></span>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6">
                                <h3 class="font-semibold text-slate-900 dark:text-white">
                                    Компоненти
                                </h3>

                                <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                                    <template x-for="item in [
                                            ['Автоматично стартиране', selectedPlugin.boot],
                                            ['Админ меню', selectedPlugin.admin_menu],
                                            ['Миграции', selectedPlugin.migrations],
                                            ['Seeders', selectedPlugin.seeders]
                                        ]" :key="item[0]">
                                        <div class="rounded-xl border border-slate-200 p-3 dark:border-slate-700">
                                            <p x-text="item[0]" class="text-xs text-slate-500"></p>

                                            <p x-text="booleanLabel(item[1])" :class="item[1]
                                                    ? 'text-emerald-600 dark:text-emerald-400'
                                                    : 'text-slate-500'" class="mt-1 text-sm font-bold"></p>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="mt-6 grid gap-3 sm:grid-cols-2">
                                <a x-show="selectedPlugin.homepage" :href="selectedPlugin.homepage" target="_blank"
                                    rel="noopener noreferrer"
                                    class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-indigo-300 hover:text-primary dark:border-slate-700 dark:text-slate-200">
                                    Страница на плъгина
                                    <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                </a>

                                <a x-show="selectedPlugin.repository" :href="selectedPlugin.repository" target="_blank"
                                    rel="noopener noreferrer"
                                    class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-indigo-300 hover:text-primary dark:border-slate-700 dark:text-slate-200">
                                    GitHub repository
                                    <i class="fa-brands fa-github"></i>
                                </a>
                            </div>

                            <div class="mt-6 rounded-xl bg-slate-50 p-4 dark:bg-slate-800">
                                <dl class="grid gap-3 text-sm sm:grid-cols-2">
                                    <div>
                                        <dt class="text-slate-500">Provider</dt>
                                        <dd x-text="selectedPlugin.provider || '—'"
                                            class="mt-1 break-all font-mono text-xs text-slate-900 dark:text-white">
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-slate-500">Лиценз</dt>
                                        <dd x-text="selectedPlugin.license || 'Не е зададен'"
                                            class="mt-1 font-semibold text-slate-900 dark:text-white"></dd>
                                    </div>
                                </dl>
                            </div>

                            <div x-show="!selectedPlugin.manifest_valid"
                                class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300">
                                <div class="flex gap-3">
                                    <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
                                    <p>
                                        Manifest файлът липсва или съдържа невалиден JSON. Показват се резервни
                                        стойности.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>
</div>
