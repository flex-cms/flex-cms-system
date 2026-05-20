<div class="p-6 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm"
    x-data="updater()">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Обновяване на системата</h2>
            <p class="text-sm text-slate-500 mt-1">
                Текуща версия на Flex CMS:
                <span
                    class="font-mono bg-slate-100 dark:bg-slate-700 px-1.5 py-0.5 rounded text-indigo-600 dark:text-indigo-400">
                    <?= $update['current_version'] ?>
                </span>
            </p>
        </div>

        <?php if ($update['has_update']): ?>
            <span
                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 animate-pulse">
                Налична е нова версия (v<?= $update['latest_version'] ?>)
            </span>
        <?php else: ?>
            <span
                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                Системата е най-новата
            </span>
        <?php endif; ?>
    </div>

    <hr class="border-slate-200 dark:border-slate-700 my-4" />

    <?php if ($update['has_update']): ?>
        <div class="bg-slate-50 dark:bg-slate-900 p-5 rounded-lg mb-6 border border-slate-100 dark:border-slate-800">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-2">Промени в новата версия:</h3>
            <p class="text-slate-700 dark:text-slate-300 text-sm leading-relaxed">
                <?= nl2br(htmlspecialchars(implode("\n", $update['changelog']))) ?>
            </p>
        </div>

        <div class="flex items-center gap-4">
            <button @click="startUpdate()" :disabled="updating"
                class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:bg-indigo-400 text-white font-semibold rounded-lg shadow-sm transition-all text-sm cursor-pointer">

                <i class="fa-solid fa-spinner fa-spin mr-2" x-show="updating" x-cloak></i>
                <i class="fa-solid fa-cloud-arrow-down mr-2" x-show="!updating"></i>

                <span x-text="updating ? 'Инсталиране...' : 'Обнови до <?= $update['latest_version'] ?>'"></span>
            </button>

            <p class="text-xs text-slate-400 max-w-sm">
                * Процесът ще замени системните файлове. Папка <strong
                    class="text-slate-600 dark:text-slate-200">plugins/</strong> няма да бъде докосната.
            </p>
        </div>
    <?php endif; ?>

    <div class="mt-8">
        <h3 class="text-md font-semibold text-slate-900 dark:text-white mb-4">История на версиите в хранилището</h3>
        <div class="border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 font-medium">
                        <th class="p-3">Версия</th>
                        <th class="p-3">Дата на издаване</th>
                        <th class="p-3">Описание / Бележки</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                    <?php foreach ($update['all_versions'] as $v): ?>
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50">
                            <td class="p-3 font-mono font-bold text-slate-700 dark:text-slate-300">
                                v<?= htmlspecialchars($v['version']) ?>
                                <?php if ($v['version'] === $update['current_version']): ?>
                                    <span
                                        class="ml-2 text-xs bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-400 px-1.5 py-0.5 rounded-md font-sans font-normal">Текуща</span>
                                <?php endif; ?>
                            </td>
                            <td class="p-3 text-slate-500 border-slate-200 dark:border-slate-700">
                                <?= htmlspecialchars($v['release_date']) ?>
                            </td>
                            <td class="p-3 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-700">
                                <?= htmlspecialchars($v['description']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="error" x-cloak
        class="mt-4 p-3 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400 rounded-lg text-sm" x-text="error">
    </div>
    <div x-show="updating" x-cloak
        class="mt-4 p-3 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-800 dark:text-indigo-300 rounded-lg text-sm flex items-center gap-2">
        <i class="fa-solid fa-circle-notch fa-spin"></i>
        <span x-text="message"></span>
    </div>
</div>
