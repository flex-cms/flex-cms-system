<?php

use Flex\Core\UI\Form;
use Flex\Core\UI\Components\Button;

$latest = $latest ?? '';
?>

<?php
Form::section(function () use ($current) {
    ?>
    <div class="flex flex-col md:flex-row gap-8 text-gray-700 dark:text-gray-300">
        <div>
            <p class="text-sm uppercase tracking-wide font-semibold text-gray-500 dark:text-gray-400">Текуща версия</p>
            <p class="text-2xl font-mono">v<?php echo $current['version']; ?></p>
        </div>
        <div>
            <p class="text-sm uppercase tracking-wide font-semibold text-gray-500 dark:text-gray-400">Дата на инсталация
            </p>
            <p class="text-2xl"><?php echo $current['release_date']; ?></p>
        </div>
    </div>
    <?php
}, 'Системна информация', 'section_system_info');
?>

<?php if (isset($error)): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded dark:bg-red-900/50 dark:text-red-200">
        <?php echo $error; ?>
    </div>
<?php else: ?>

    <?php if ($needsUpdate): ?>
        <div x-data="updater" class="space-y-4">
            <?php
            Form::section(function () use ($latest) {
                ?>
                <div class="space-y-4">
                    <h4 class="text-lg font-bold text-blue-900 dark:text-blue-100">
                        Налична е нова версия: v<?php echo $latest['latest_version']; ?>
                    </h4>

                    <h5 class="font-semibold text-gray-700 dark:text-gray-300">Какво е новото:</h5>
                    <ul class="list-disc list-inside space-y-1 text-gray-600 dark:text-gray-400">
                        <?php foreach ($latest['changelog'] as $change): ?>
                            <li><?php echo $change; ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="pt-4">
                        <?php echo Button::make('Инсталирай актуализацията')
                            ->icon('fa fa-sync')
                            ->loading('isUpdating', 'Актуализиране...')
                            ->attr('@click="startUpdate()"');
                        ?>
                    </div>
                </div>
                <?php
            }, 'Налична актуализация', 'section_update_available');
            ?>
        </div>
    <?php else: ?>
        <div
            class="flex items-center gap-3 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 p-4 rounded-xl text-green-800 dark:text-green-300">
            <i class="fa fa-check-circle text-xl"></i>
            <span>Системата е актуална! (v<?php echo $current['version']; ?>)</span>
        </div>
    <?php endif; ?>

<?php endif; ?>