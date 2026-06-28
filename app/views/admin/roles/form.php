<?php

use Flex\Core\UI\Form;
use Flex\Core\UI\Page;

$permissions = $permissions ?? [];
$role = $role ?? null;
$assigned = $assignedPermissions ?? [];

$isEdit = isset($role->id);
$action = $isEdit ? '/admin/users/roles/edit/' . $role->id : '/admin/users/roles/create';

Page::header(
    title: $role ? 'Редактиране на роля' : 'Създаване на нова роля',
    backUrl: '/admin/users/roles',
    subtitle: $role ? 'Променете параметрите и настройките на ролята' : 'Създайте нова роля за потребителите на сайта си'
);
?>

<?php Form::create(['action' => $action, 'method' => 'POST', 'class' => 'max-w-5xl']) ?>

    <?php Form::section(title: 'Обща информация', slot: function () use ($role) { ?>

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

            <?php Form::input('priority', 'Ниво на приоритет', [
                'type' => 'number',
                'value' => $role?->priority ?? 0,
                'placeholder' => 'напр. 10'
            ]); ?>

        <?php }, ['md' => 2, 'lg' => 3]); ?>

        <?php Form::row(function () use ($role) { ?>
            <?php Form::input('priority', 'Ниво на приоритет', [
                'type' => 'number',
                'value' => $role?->priority ?? 0,
                'placeholder' => 'напр. 10'
            ]); ?>

            <?php Form::color('color', 'Цвят на ролята', [
                'value' => $role?->color ?? '#6366f1'
            ]); ?>
        <?php }, ['md' => 2]); ?>

        <?php Form::textarea('description', 'Описание', [
            'value' => $role?->description,
            'placeholder' => 'За какво отговаря тази роля...',
            'rows' => 5
        ]); ?>

        <?php Form::row(function () use ($role) { ?>

            <?php Form::toggle('is_default', 'Роля по подразбиране', [
                'value' => $role?->is_default ?? false,
                'description' => 'Автоматично добавяне при регистрация. Само една роля може да бъде активна по подразбиране.'
            ]); ?>

            <?php Form::toggle('is_active', 'Активна роля', [
                'value' => $role?->is_active ?? true,
                'description' => 'Деактивирането спира достъпа на потребителите с тази роля.'
            ]); ?>

        <?php }, ['md' => 2]); ?>

    <?php }); ?>

    <?php Form::section(title: 'Разрешения на ролята', slot: function () use ($permissions, $assigned) { ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php foreach ($permissions as $module => $list): ?>
                <?php foreach ($list as $p): ?>
                    <div class="p-4 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-lg">
                        <?php Form::toggle('permissions[' . $p->id . ']', $p->name, [
                            'value' => is_array($assigned) && in_array($p->id, $assigned),
                            'description' => $p->description ?? ''
                        ]); ?>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    <?php }); ?>

    <?php Form::section(title: 'График на достъп', slot: function () use ($role) {

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
                    $days = [1 => ['Пон', 'Понеделник'], 2 => ['Вто', 'Вторник'], 3 => ['Сря', 'Сряда'], 4 => ['Чет', 'Четвъртък'], 5 => ['Пет', 'Петък'], 6 => ['Съб', 'Събота'], 7 => ['Нед', 'Неделя']];
                    foreach ($days as $dayNum => $dayLabel):
                        $dayData = $schedule[$dayNum] ?? null;
                        $isDayActive = isset($dayData);
                        ?>
                        <div x-data="{ dayActive: <?= $isDayActive ? 'true' : 'false' ?> }"
                            class="p-4 rounded-2xl border transition-all"
                            :class="dayActive ? 'bg-white dark:bg-slate-800 border-indigo-500/30' : 'bg-slate-50/50 dark:bg-slate-900/20 border-slate-200 dark:border-slate-700'">
                            <div class="flex flex-wrap items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <?php Form::toggle("schedule[$dayNum][active]", $dayLabel[1], [
                                        'value' => $isDayActive,
                                        'id' => "day_$dayNum",
                                        'attr' => [
                                            'x-model' => 'dayActive',
                                            '@change' => 'dayActive = $el.checked'
                                        ]
                                    ]); ?>
                                </div>

                                <div class="flex items-center gap-4" x-show="dayActive" x-cloak>
                                    <?php Form::input("schedule[$dayNum][start]", 'От', ['type' => 'time', 'value' => $dayData['start'] ?? '09:00', 'class' => '!mb-0 !py-1.5 w-32']); ?>
                                    <span class="text-slate-400">—</span>
                                    <?php Form::input("schedule[$dayNum][end]", 'До', ['type' => 'time', 'value' => $dayData['end'] ?? '18:00', 'class' => '!mb-0 !py-1.5 w-32']); ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div x-show="!hasTimeLimit" x-cloak
                class="p-10 text-center border border-slate-100 dark:border-slate-700 rounded-md bg-slate-50/50 dark:bg-slate-800/50">
                <i class="fa-solid fa-infinity text-4xl text-slate-400 mb-4"></i>
                <h3 class="text-xl font-semibold mb-2">Пълен достъп без ограничения</h3>
                <p class="text-slate-500 dark:text-slate-400 max-w-md mx-auto leading-relaxed">
                    Функцията за сложен график в момента не е включена. Потребителите с тази роля имат пълен достъп до системата
                    без времеви рестрикции.
                </p>
            </div>
        </div>

    <?php }); ?>

    <?php Form::submit($isEdit ? 'Запазване на промените' : 'Създаване на роля', 'fa-save'); ?>

<?php Form::close(); ?>