<?php

use Flex\Core\UI\Table;
?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">
            <?= $title ?>
        </h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($themes as $theme): ?>
            <div
                class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden transition-all hover:shadow-md">
                <div class="h-40 bg-slate-100 dark:bg-slate-900 flex items-center justify-center text-slate-400">
                    <i class="fas fa-paint-brush text-4xl"></i>
                </div>

                <div class="p-5">
                    <div class="flex justify-between items-start mb-2">
                        <h2 class="text-lg font-bold text-slate-800 dark:text-white">
                            <?= $theme->name ?>
                        </h2>
                        <?php if ($theme->is_active): ?>
                            <span
                                class="px-2 py-1 text-[10px] font-bold uppercase bg-green-100 text-green-700 rounded">Активна</span>
                        <?php endif; ?>
                    </div>

                    <p class="text-sm text-slate-500 mb-4">
                        <?= $theme->description ?? 'Няма описание.' ?>
                    </p>

                    <div
                        class="border-t border-slate-100 dark:border-slate-700 pt-4 flex justify-between items-center text-sm">
                        <span class="text-slate-400">v
                            <?= $theme->version ?>
                        </span>

                        <?php if (!$theme->is_active): ?>
                            <form action="/admin/themes/activate" method="POST">
                                <input type="hidden" name="folder" value="<?= $theme->folder ?>">
                                <button type="submit"
                                    class="px-3 py-1.5 bg-indigo-600 text-white rounded hover:bg-indigo-700 transition">
                                    Активирай
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="flex justify-end">
                                <?= Table::actionLink("/admin/themes/theme-settings", 'Настройки', 'fas fa-cog') ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>