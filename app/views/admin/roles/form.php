<?php

use Flex\Core\UI\Form;
use Flex\Core\UI\Page;

$permissions = $permissions ?? [];
$role = $role ?? null;

Page::header(
    title: $role ? 'Редактиране на роля' : 'Създаване на нова роля',
    backUrl: '/admin/roles',
    subtitle: 'Дефинирайте името и специфичните разрешения за достъп'
);
?>

<form action="<?= $role ? '/admin/roles/edit/' . $role->id : '/admin/roles/create' ?>" method="POST" class="max-w-5xl">

    <?php Form::section(function () use ($role) { ?>
        <?php Form::row(function () use ($role) { ?>
            <?php Form::input('name', 'Име на ролята', [
                'value' => $role?->name,
                'placeholder' => 'напр. Модератор',
                'required' => true
            ]); ?>

            <?php Form::input('slug', 'Уникален идентификатор (Slug)', [
                'value' => $role?->slug,
                'placeholder' => 'напр. moderator',
            ]); ?>
        <?php }); ?>

        <?php Form::row(function () use ($role) { ?>
            <?php Form::input('priority', 'Ниво на приоритет', [
                'type' => 'number',
                'value' => $role?->priority ?? 0,
                'placeholder' => 'напр. 10'
            ]); ?>

            <?php Form::color('color', 'Цвят на ролята', [
                'value' => $role?->color ?? '#6366f1'
            ]); ?>
        <?php }); ?>

        <?php Form::textarea('description', 'Описание', [
            'value' => $role?->description,
            'placeholder' => 'За какво отговаря тази роля...',
            'rows' => 5
        ]); ?>

        <div class="mt-4 flex gap-6">
        </div>
    <?php }, 'Обща информация'); ?>

    <?php Form::section(function () use ($role) {
        $schedule = $role?->options['schedule'] ?? [];
        $hasGlobalLimit = !empty($schedule);
        ?>
        <div x-data="{ 
        hasTimeLimit: <?= $hasGlobalLimit ? 'true' : 'false' ?>,
        activeDays: <?= json_encode(array_keys($schedule)) ?> 
    }">
            <div class="mb-6">
                <?php Form::toggle('has_time_limit', 'Активиране на сложен график', [
                    'value' => $hasGlobalLimit,
                    'description' => 'Дефинирайте специфично работно време за всеки ден от седмицата.',
                    'attr' => ['@change' => 'hasTimeLimit = $event.target.checked']
                ]); ?>
            </div>

            <div x-show="hasTimeLimit" x-collapse x-cloak>
                <div class="space-y-3">
                    <?php
                    $days = [
                        1 => ['Пон', 'Понеделник'],
                        2 => ['Вто', 'Вторник'],
                        3 => ['Сря', 'Сряда'],
                        4 => ['Чет', 'Четвъртък'],
                        5 => ['Пет', 'Петък'],
                        6 => ['Съб', 'Събота'],
                        7 => ['Нед', 'Неделя']
                    ];

                    foreach ($days as $dayNum => $dayLabel):
                        $dayData = $schedule[$dayNum] ?? null;
                        $isDayActive = isset($dayData);
                        ?>
                        <div x-data="{ dayActive: <?= $isDayActive ? 'true' : 'false' ?> }"
                            class="p-4 rounded-2xl border transition-all" :class="dayActive 
                                ? 'bg-white dark:bg-slate-800 border-primary/30 shadow-sm ring-1 ring-primary/10' 
                                : 'bg-slate-50/50 dark:bg-slate-900/20 border-slate-200 dark:border-slate-800'">

                            <div class="flex flex-wrap items-center justify-between gap-4">
                                <div class="flex items-center gap-3 min-w-40">
                                    <input type="checkbox" name="schedule[<?= $dayNum ?>][active]" id="day_<?= $dayNum ?>"
                                        class="sr-only peer" x-model="dayActive">

                                    <label for="day_<?= $dayNum ?>"
                                        class="relative w-10 h-5 bg-slate-300 dark:bg-slate-700 rounded-full cursor-pointer peer-checked:bg-primary transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-5"></label>

                                    <span class="font-bold" :class="dayActive ? 'text-primary' : 'text-slate-500'">
                                        <?= $dayLabel[1] ?>
                                    </span>
                                </div>

                                <div class="flex items-center gap-4" x-show="dayActive" x-transition:enter.duration.300ms>
                                    <div class="flex items-center gap-2">
                                        <?php Form::input("schedule[$dayNum][start]", 'От', [
                                            'type' => 'time',
                                            'value' => $dayData['start'] ?? '09:00',
                                            'class' => '!mb-0 !py-1.5 w-32 focus:border-primary focus:ring-primary/20'
                                        ]); ?>

                                        <span class="text-slate-400 mt-6">—</span>

                                        <?php Form::input("schedule[$dayNum][end]", 'До', [
                                            'type' => 'time',
                                            'value' => $dayData['end'] ?? '18:00',
                                            'class' => '!mb-0 !py-1.5 w-32 focus:border-primary focus:ring-primary/20'
                                        ]); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div x-show="!hasTimeLimit" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                class="relative overflow-hidden p-10 bg-white dark:bg-slate-900/40 border border-slate-100 dark:border-slate-700 rounded-md shadow-sm">

                <div class="absolute -right-8 -top-8 text-slate-50 dark:text-slate-800/50 pointer-events-none">
                    <i class="fa-solid fa-clock-rotate-left text-9xl"></i>
                </div>

                <div class="relative z-10 flex flex-col items-center text-center">
                    <div
                        class="w-16 h-16 bg-slate-50 dark:bg-slate-800 rounded-2xl flex items-center justify-center mb-4 shadow-inner">
                        <i class="fa-solid fa-infinity text-2xl text-slate-400 dark:text-slate-500"></i>
                    </div>

                    <h3 class="text-xl font-semibold text-slate-800 dark:text-slate-200 tracking-tight">
                        Пълен достъп без ограничения
                    </h3>
                    <p class="text-slate-500 dark:text-slate-400 mt-1 max-w-xs leading-relaxed">
                        Тази роля разполага с перманентни права. Потребителите могат да оперират в системата 24/7.
                    </p>

                    <div
                        class="mt-6 flex items-center gap-2 px-3 py-1 bg-emerald-500/10 dark:bg-emerald-500/5 rounded-full border border-emerald-500/20">
                        <span class="relative flex h-2 w-2">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span class="font-semibold text-emerald-600 dark:text-emerald-500">Винаги активен</span>
                    </div>
                </div>
            </div>
        </div>
    <?php }, 'График на достъп'); ?>

    <?php Form::section(function () use ($role) { ?>
        <div class="space-y-4">
            <?php Form::toggle('is_default', 'Роля по подразбиране', [
                'value' => $role?->is_default ?? false,
                'description' => 'Ако е активно, системата автоматично ще добавя тази роля към профила на всеки нов потребител при регистрация.'
            ]); ?>

            <?php Page::alert('info', 'Важно!', 'Ако маркирате тази роля като „по подразбиране“, системата автоматично ще премахне този статус от всяка друга роля, тъй като само една роля може да бъде активна по подразбиране.'); ?>

            <?php Form::toggle('is_active', 'Активна роля', [
                'value' => $role?->is_active ?? true,
                'description' => 'Ако деактивирате ролята, потребителите с тази роля няма да могат да използват свързаните с нея права.'
            ]); ?>
        </div>
    <?php }, 'Автоматизация и статус'); ?>

    <?php Form::submit($role ? 'Запазване' : 'Създаване', 'fa-save'); ?>

</form>