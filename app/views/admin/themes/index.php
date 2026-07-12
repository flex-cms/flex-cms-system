<?php

use Flex\Core\UI\Components\Button;
use Flex\Core\UI\Form;
?>

<div class="space-y-6" x-data="{ openModal: null }">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">
            <?= $title ?>
        </h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($themes as $theme): ?>
            <div @click="openModal = '<?= $theme->folder ?>'"
                class="bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden transition-all hover:shadow-md cursor-pointer group">

                <?php if ($theme->screenshot): ?>
                    <img src="<?= $theme->screenshot ?>" alt="<?= $theme->name ?>"
                        class="w-full aspect-4/3 object-cover group-hover:scale-[1.02] transition-transform duration-300">
                <?php else: ?>
                    <div
                        class="w-full aspect-4/3 bg-slate-100 dark:bg-slate-900 flex items-center justify-center text-slate-400">
                        <i class="fas fa-paint-brush text-4xl"></i>
                    </div>
                <?php endif; ?>

                <div class="p-5">
                    <div class="flex justify-between items-start mb-2">
                        <h2
                            class="text-xl font-semibold text-slate-800 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                            <?= $theme->name ?>
                        </h2>
                        <?php if ($theme->is_active): ?>
                            <span
                                class="px-2 py-1 text-[10px] font-bold uppercase bg-green-100 text-green-700 rounded">Активна</span>
                        <?php endif; ?>
                    </div>

                    <p class="text-slate-500 dark:text-slate-400 mb-5 line-clamp-2">
                        <?= $theme->description ?? 'Няма описание.' ?>
                    </p>

                    <div class="border-t border-slate-100 dark:border-slate-700 pt-4 flex justify-between items-center text-sm"
                        @click.stop>
                        <span class="text-slate-400">v
                            <?= $theme->version ?>
                        </span>

                        <?php if (!$theme->is_active): ?>
                            <?php
                            Form::create(['action' => '/admin/themes/activate', 'method' => 'POST']);
                            echo '<input type="hidden" name="folder" value="' . $theme->folder . '">';
                            echo Button::make('Активиране')
                                ->variant('primary')
                                ->render();
                            Form::close();
                            ?>
                        <?php else: ?>
                            <div class="flex justify-end gap-2">
                                <?php
                                Form::create(['action' => '/admin/themes/deactivate', 'method' => 'POST']);
                                echo '<input type="hidden" name="folder" value="' . $theme->folder . '">';
                                echo Button::make('Деактивиране')
                                    ->variant('secondary')
                                    ->render();
                                Form::close();
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div x-show="openModal === '<?= $theme->folder ?>'"
                class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-x-hidden overflow-y-auto" x-cloak>

                <div x-show="openModal === '<?= $theme->folder ?>'" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" @click="openModal = null"
                    class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"></div>

                <div x-show="openModal === '<?= $theme->folder ?>'" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative bg-white dark:bg-slate-800 rounded-xl shadow-xl max-w-4xl w-full overflow-hidden border border-slate-200 dark:border-slate-700 z-10">

                    <button @click="openModal = null"
                        class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 z-20 bg-slate-100 dark:bg-slate-700/50 p-2 rounded-full w-8 h-8 flex items-center justify-center transition-colors">
                        <i class="fas fa-times"></i>
                    </button>

                    <div class="grid grid-cols-1 md:grid-cols-2">
                        <div class="bg-slate-900 flex items-center justify-center aspect-4/3 md:aspect-auto md:h-full">
                            <?php if ($theme->screenshot): ?>
                                <img src="<?= $theme->screenshot ?>" alt="<?= $theme->name ?>"
                                    class="w-full h-full object-cover">
                            <?php else: ?>
                                <div class="text-slate-500 flex flex-col items-center gap-3">
                                    <i class="fas fa-paint-brush text-6xl"></i>
                                    <span class="text-sm">Няма наличен скрийншот</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="p-6 flex flex-col justify-between space-y-6">
                            <div>
                                <div class="flex items-center gap-3 mb-2">
                                    <h3 class="text-2xl font-bold text-slate-900 dark:text-white">
                                        <?= $theme->name ?>
                                    </h3>
                                    <span
                                        class="text-xs font-semibold px-2 py-0.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-full">
                                        v
                                        <?= $theme->version ?>
                                    </span>
                                </div>

                                <p class="text-slate-600 dark:text-slate-300 text-sm leading-relaxed mb-4">
                                    <?= $theme->description ?? 'Няма описание.' ?>
                                </p>

                                <div class="space-y-2 text-sm border-t border-slate-100 dark:border-slate-700 pt-4">
                                    <div class="flex justify-between">
                                        <span class="text-slate-400">Автор:</span>
                                        <span class="font-medium text-slate-700 dark:text-slate-200">
                                            <?php if (isset($theme->author['website'])): ?>
                                                <a href="<?= $theme->author['website'] ?>" target="_blank"
                                                    class="text-indigo-500 hover:underline">
                                                    <?= $theme->author['name'] ?? 'Неизвестен' ?>
                                                </a>
                                            <?php else: ?>
                                                <?= $theme->author['name'] ?? 'Неизвестен' ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-400">Лиценз:</span>
                                        <span class="font-medium text-slate-700 dark:text-slate-200">
                                            <?php if (isset($theme->license_url)): ?>
                                                <a href="<?= $theme->license_url ?>" target="_blank"
                                                    class="text-indigo-500 hover:underline">
                                                    <?= $theme->license ?>
                                                </a>
                                            <?php else: ?>
                                                <?= $theme->license ?? 'MIT' ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <?php if (isset($theme->homepage)): ?>
                                        <div class="flex justify-between">
                                            <span class="text-slate-400">Уебсайт:</span>
                                            <a href="<?= $theme->homepage ?>" target="_blank"
                                                class="font-medium text-indigo-500 hover:underline">Преглед на живо</a>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($theme->keywords)): ?>
                                    <div class="mt-4 flex flex-wrap gap-1.5">
                                        <?php foreach ($theme->keywords as $keyword): ?>
                                            <span
                                                class="text-[11px] bg-slate-50 dark:bg-slate-900 dark:text-slate-400 text-slate-500 border border-slate-200 dark:border-slate-700 px-2 py-0.5 rounded">
                                                #
                                                <?= $keyword ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div
                                class="flex items-center justify-between border-t border-slate-100 dark:border-slate-700 pt-4">
                                <div>
                                    <?php if ($theme->is_active): ?>
                                        <span
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium bg-green-50 text-green-700 dark:bg-green-950/30 dark:text-green-400 rounded-md">
                                            <span class="w-2 h-2 rounded-full bg-green-500"></span> Текущо активна
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="flex gap-2">
                                    <button @click="openModal = null"
                                        class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg transition-colors">
                                        Затвори
                                    </button>

                                    <?php if (!$theme->is_active): ?>
                                        <?php
                                        Form::create(['action' => '/admin/themes/activate', 'method' => 'POST']);
                                        echo '<input type="hidden" name="folder" value="' . $theme->folder . '">';
                                        echo Button::make('Активиране')->variant('primary')->render();
                                        Form::close();
                                        ?>
                                    <?php else: ?>
                                        <?php
                                        Form::create(['action' => '/admin/themes/deactivate', 'method' => 'POST']);
                                        echo '<input type="hidden" name="folder" value="' . $theme->folder . '">';
                                        echo Button::make('Деактивиране')->variant('secondary')->render();
                                        Form::close();
                                        ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
