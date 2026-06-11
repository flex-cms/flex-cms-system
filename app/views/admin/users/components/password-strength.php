<?php

use Flex\Core\UI\Components\Button;
use Flex\Core\UI\Form;
?>

<div x-data="passwordStrength" class="space-y-4">

    <div class="flex items-end gap-3">
        <div class="flex-1">
            <?php Form::input('password', 'Парола', [
                'value' => '',
                'placeholder' => $user ? 'Оставете празно, за да запазите текущата' : 'Въведете парола',
                ':type' => "showPassword ? 'text' : 'password'",
                'x-model' => 'password'
            ]); ?>

            <div class="flex flex-wrap gap-2 mt-2">
                <?= Button::make('Генериране на сигурна парола')
                    ->type('button')
                    ->attr('@click="generatePassword()"')
                    ->variant('secondary')
                    ->fontSize('17px')
                    ->size('md')
                    ?>

                <?= Button::make('Покажи паролата')
                    ->type('button')
                    ->attr('@click="togglePassword()"')
                    ->variant('secondary')
                    ->fontSize('17px')
                    ->size('md')
                    ->toggle('showPassword', 'fa-solid fa-eye-slash', 'Скриване на паролата')
                    ?>
            </div>
        </div>
    </div>

    <template x-if="score >= 0">
        <div class="mt-3 space-y-1.5">
            <div class="flex gap-1 h-1.5 rounded-full overflow-hidden bg-slate-200 dark:bg-slate-700">
                <div class="h-full transition-all duration-300" :class="{
                    'w-1/4 bg-rose-500': score <= 1,
                    'w-2/4 bg-amber-500': score === 2,
                    'w-3/4 bg-yellow-500': score === 3,
                    'w-full bg-emerald-500': score === 4
                }"></div>
            </div>

            <div class="text-xs font-medium flex justify-between">
                <span class="text-slate-500 dark:text-slate-400">Сигурност:</span>
                <span :class="{
                    'text-rose-600 dark:text-rose-400': score <= 1,
                    'text-amber-600 dark:text-amber-400': score === 2,
                    'text-yellow-600 dark:text-yellow-400': score === 3,
                    'text-emerald-600 dark:text-emerald-400': score === 4
                }"
                    x-text="score <= 1 ? 'Слаба' : (score === 2 ? 'Умерена' : (score === 3 ? 'Силна' : 'Много силна'))"></span>
            </div>
        </div>
    </template>

    <div class="mt-4">
        <?php Form::input('password_confirmation', 'Повторете паролата', [
            'value' => '',
            'placeholder' => 'Въведете паролата отново',
            ':type' => "showPassword ? 'text' : 'password'",
            'x-model' => 'password_confirmation'
        ]); ?>

        <template x-if="password_confirmation !== ''">
            <p class="text-xs font-medium mt-1.5"
                :class="password === password_confirmation ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                <span x-text="password === password_confirmation ? 'Паролите съвпадат' : 'Паролите не съвпадат'"></span>
            </p>
        </template>
    </div>

</div>