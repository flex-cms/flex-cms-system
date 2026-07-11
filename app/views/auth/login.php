<?php

use Flex\Core\UI\Components\Alert;
use Flex\Core\UI\Components\Button;
use Flex\Core\UI\Components\InputField;
use Flex\Core\UI\Components\Link;
use Flex\Core\UI\Form;
?>

<div class="flex items-center justify-center mt-5 md:mt-10 py-5 md:py-10 px-4 sm:px-6 lg:px-8">
    <div
        class="max-w-md w-full space-y-8 bg-white dark:bg-gray-800 p-5 md:p-10 max-sm:text-sm rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700">

        <div class="text-center">
            <?php Form::heading('Вход', 'h2', 'text-2xl font-semibold text-gray-900 dark:text-white'); ?>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Влизане в административната зона на сайта. Моля, въведете вашите данни за достъп по-долу.
            </p>
        </div>

        <?= Alert::renderFromSession() ?>

        <?php Form::create([
            'action' => '/login',
            'method' => 'POST'
        ]); ?>
        
        <?= InputField::make('email', 'Имейл адрес')
            ->type('email')
            ->placeholder('Имейл адресът, с който сте регистрирани.')
            ->value($old['email'] ?? '')
            ->required();
        ?>

        <?= InputField::make('password', 'Парола')
            ->type('password')
            ->placeholder('Вашата паролата')
            ->required();
        ?>

        <div class="flex items-center justify-between">
            <?php Form::toggle('remember', 'Запомни ме', [
                'value' => false
            ]); ?>

            <?= Link::make('/password/forgot', 'Забравена парола?'); ?>
        </div>

        <div class="w-full">
            <?= Button::make('Вход в профила')
                ->type('submit')
                ->addClasses('w-full')
                ->loading('isSubmitting', 'Моля, изчакайте...');
            ?>
        </div>

        <?php Form::close(); ?>

    </div>
</div>
