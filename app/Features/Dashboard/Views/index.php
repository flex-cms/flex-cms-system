<?php

use Flex\Core\Helpers\DateHelper;

$stats = $stats ?? [];
$recentPages = $recentPages ?? collect();
$recentLogins = $recentLogins ?? collect();
$system = $system ?? [];

$cards = [
    [
        'label' => 'Потребители',
        'value' => $stats['users_count'] ?? 0,
        'detail' => ($stats['active_users_count'] ?? 0) . ' активни профила',
        'icon' => 'fa-users',
        'accent' => 'blue',
        'url' => '/admin/users/index',
    ],
    [
        'label' => 'Активни страници',
        'value' => $stats['active_pages_count'] ?? 0,
        'detail' => ($stats['pages_count'] ?? 0) . ' страници общо',
        'icon' => 'fa-file-lines',
        'accent' => 'indigo',
        'url' => '/admin/pages',
    ],
    [
        'label' => 'Активни плъгини',
        'value' => $stats['active_plugins_count'] ?? 0,
        'detail' => ($stats['plugins_count'] ?? 0) . ' инсталирани общо',
        'icon' => 'fa-puzzle-piece',
        'accent' => 'violet',
        'url' => '/admin/plugins',
    ],
    [
        'label' => 'Активни менюта',
        'value' => $stats['active_menus_count'] ?? 0,
        'detail' => ($stats['menus_count'] ?? 0) . ' менюта общо',
        'icon' => 'fa-bars-staggered',
        'accent' => 'emerald',
        'url' => '/admin/menus',
    ],
];

$accentClasses = [
    'blue' => 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400',
    'indigo' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400',
    'violet' => 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400',
    'emerald' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
];
?>

<div class="space-y-6">
    <section
        class="relative overflow-hidden rounded-2xl bg-linear-to-br from-indigo-600 via-indigo-600 to-violet-600 px-6 py-7 text-white shadow-lg shadow-indigo-500/15 md:px-8">
        <div class="absolute -right-16 -top-20 h-56 w-56 rounded-full bg-white/10"></div>
        <div class="absolute -bottom-24 right-24 h-48 w-48 rounded-full bg-violet-300/10"></div>

        <div class="relative flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="mb-2 text-sm font-semibold uppercase text-indigo-100">Flex CMS</p>
                <h2 class="text-2xl font-bold md:text-3xl">Добре дошли в таблото за управление</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-indigo-100 md:text-base">
                    Следете съдържанието, потребителите и състоянието на системата от едно място.
                </p>
            </div>

            <a data-turbo="false" href="/admin/pages/create"
                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-bold text-indigo-700 shadow-sm transition hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-white/70">
                <i class="fa-solid fa-plus"></i>
                Нова страница
            </a>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <?php foreach ($cards as $card): ?>
            <a data-turbo="false" href="<?= htmlspecialchars($card['url'], ENT_QUOTES, 'UTF-8') ?>"
                class="group rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md dark:border-slate-700 dark:bg-slate-800 dark:hover:border-indigo-500/50">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                            <?= htmlspecialchars($card['label'], ENT_QUOTES, 'UTF-8') ?>
                        </p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-white">
                            <?= number_format((int) $card['value'], 0, ',', ' ') ?>
                        </p>
                    </div>
                    <span
                        class="flex h-12 w-12 items-center justify-center rounded-xl <?= $accentClasses[$card['accent']] ?>">
                        <i class="fa-solid <?= htmlspecialchars($card['icon'], ENT_QUOTES, 'UTF-8') ?> text-lg"></i>
                    </span>
                </div>
                <div
                    class="mt-5 flex items-center justify-between gap-3 border-t border-slate-100 pt-4 text-xs dark:border-slate-700">
                    <span class="text-slate-500 dark:text-slate-400">
                        <?= htmlspecialchars($card['detail'], ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <i
                        class="fa-solid fa-arrow-right text-slate-300 transition group-hover:translate-x-1 group-hover:text-indigo-500 dark:text-slate-600"></i>
                </div>
            </a>
        <?php endforeach; ?>
    </section>

    <section class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div
            class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800 xl:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-700">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white">Последно редактирани страници</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Последните промени по съдържанието на
                        сайта</p>
                </div>
                <a data-turbo="false" href="/admin/pages"
                    class="text-sm font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Всички</a>
            </div>

            <?php if ($recentPages->isEmpty()): ?>
                <div class="px-6 py-12 text-center">
                    <i class="fa-regular fa-file-lines text-3xl text-slate-300 dark:text-slate-600"></i>
                    <p class="mt-3 text-sm font-medium text-slate-600 dark:text-slate-300">Все още няма създадени страници
                    </p>
                    <a data-turbo="false" href="/admin/pages/create"
                        class="mt-2 inline-block text-sm font-semibold text-indigo-600 hover:underline dark:text-indigo-400">Създай
                        първата страница</a>
                </div>
            <?php else: ?>
                <div class="divide-y divide-slate-100 dark:divide-slate-700">
                    <?php foreach ($recentPages as $page): ?>
                        <a data-turbo="false" href="/admin/pages/edit/<?= (int) $page->id ?>"
                            class="flex items-center gap-4 px-5 py-4 transition hover:bg-slate-50 dark:hover:bg-slate-700/40">
                            <span
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-300">
                                <i class="fa-regular fa-file-lines"></i>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-sm font-semibold text-slate-800 dark:text-slate-100">
                                    <?= htmlspecialchars($page->name, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                                <span class="mt-1 block truncate text-xs text-slate-500 dark:text-slate-400">
                                    <?= htmlspecialchars($page->full_slug ?: '/', ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </span>
                            <span class="hidden text-right sm:block">
                                <span class="block text-xs text-slate-500 dark:text-slate-400">
                                    <?= $page->updated_at ? htmlspecialchars(DateHelper::format($page->updated_at, true), ENT_QUOTES, 'UTF-8') : '—' ?>
                                </span>
                                <span
                                    class="mt-1 inline-flex items-center gap-1 text-xs <?= $page->is_active ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' ?>">
                                    <span
                                        class="h-1.5 w-1.5 rounded-full <?= $page->is_active ? 'bg-emerald-500' : 'bg-slate-400' ?>"></span>
                                    <?= $page->is_active ? 'Активна' : 'Неактивна' ?>
                                </span>
                            </span>
                            <i class="fa-solid fa-chevron-right text-xs text-slate-300 dark:text-slate-600"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <aside
            class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <div class="flex items-center gap-3">
                <span
                    class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <i class="fa-solid fa-server"></i>
                </span>
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white">Състояние на системата</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Основна техническа информация</p>
                </div>
            </div>

            <dl class="mt-5 divide-y divide-slate-100 text-sm dark:divide-slate-700">
                <div class="flex items-center justify-between gap-4 py-3">
                    <dt class="text-slate-500 dark:text-slate-400">Flex CMS</dt>
                    <dd class="font-semibold text-slate-800 dark:text-slate-100">v
                        <?= htmlspecialchars($system['version'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                    </dd>
                </div>
                <div class="flex items-center justify-between gap-4 py-3">
                    <dt class="text-slate-500 dark:text-slate-400">Дата на версията</dt>
                    <dd class="font-semibold text-slate-800 dark:text-slate-100">
                        <?= !empty($system['release_date']) ? htmlspecialchars(DateHelper::format($system['release_date']), ENT_QUOTES, 'UTF-8') : '—' ?>
                    </dd>
                </div>
                <div class="flex items-center justify-between gap-4 py-3">
                    <dt class="text-slate-500 dark:text-slate-400">PHP</dt>
                    <dd class="font-semibold text-slate-800 dark:text-slate-100">
                        <?= htmlspecialchars($system['php_version'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                    </dd>
                </div>
                <div class="flex items-center justify-between gap-4 py-3">
                    <dt class="text-slate-500 dark:text-slate-400">Активна тема</dt>
                    <dd class="max-w-36 truncate font-semibold text-slate-800 dark:text-slate-100">
                        <?= htmlspecialchars($system['active_theme'] ?? '—', ENT_QUOTES, 'UTF-8') ?>
                    </dd>
                </div>
            </dl>

            <a data-turbo="false" href="/admin/update"
                class="mt-5 flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 dark:border-slate-700 dark:text-slate-200 dark:hover:border-indigo-500/50 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-300">
                <i class="fa-solid fa-arrows-rotate"></i>
                Проверка за обновяване
            </a>
        </aside>
    </section>

    <section class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div
            class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800 xl:col-span-2">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 dark:border-slate-700">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white">Последни вписвания</h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Последна активност на администраторите и
                        потребителите</p>
                </div>
                <a data-turbo="false" href="/admin/users/index"
                    class="text-sm font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">Потребители</a>
            </div>

            <?php if ($recentLogins->isEmpty()): ?>
                <div class="px-6 py-10 text-center text-sm text-slate-500 dark:text-slate-400">Все още няма регистрирани
                    вписвания.</div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead
                            class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-900/40 dark:text-slate-400">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Потребител</th>
                                <th class="px-5 py-3 font-semibold">Последен вход</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm dark:divide-slate-700">
                            <?php foreach ($recentLogins as $user): ?>
                                <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-700/40">
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <span
                                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 font-bold text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300">
                                                <?= htmlspecialchars(mb_strtoupper(mb_substr($user->fullname ?: $user->email, 0, 1)), ENT_QUOTES, 'UTF-8') ?>
                                            </span>
                                            <span class="min-w-0">
                                                <span class="block truncate font-semibold text-slate-800 dark:text-slate-100">
                                                    <?= htmlspecialchars($user->fullname ?: 'Без име', ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                                <span class="block truncate text-xs text-slate-500 dark:text-slate-400">
                                                    <?= htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8') ?>
                                                </span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-3.5 text-slate-500 dark:text-slate-400">
                                        <?= htmlspecialchars(DateHelper::format($user->last_login, true), ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <aside
            class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <h3 class="font-bold text-slate-900 dark:text-white">Бързи действия</h3>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Най-често използваните секции</p>
            <div class="mt-5 space-y-2.5">
                <a data-turbo="false" href="/admin/pages/create"
                    class="flex items-center gap-3 rounded-lg bg-indigo-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-indigo-700">
                    <i class="fa-solid fa-plus w-5 text-center"></i>
                    Нова страница
                </a>
                <a data-turbo="false" href="/admin/menus"
                    class="flex items-center gap-3 rounded-lg border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700/50">
                    <i class="fa-solid fa-bars-staggered w-5 text-center text-emerald-500"></i>
                    Управление на менюта
                </a>
                <a data-turbo="false" href="/admin/settings/general"
                    class="flex items-center gap-3 rounded-lg border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700/50">
                    <i class="fa-solid fa-gear w-5 text-center text-slate-400"></i>
                    Общи настройки
                </a>
            </div>
        </aside>
    </section>
</div>
