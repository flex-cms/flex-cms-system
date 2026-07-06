<?php

use Flex\Core\UI\Components\Alert;
use Flex\Core\UI\Components\Button;
use Flex\Core\UI\Components\InputField;
use Flex\Core\UI\Components\Link;
use Flex\Core\UI\Form;
?>

<div class="flex items-center justify-center mt-5 md:mt-10 py-5 md:py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white dark:bg-gray-800 p-10 max-sm:text-sm rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700">

        <div>
            <h2 class="text-center text-3xl font-extrabold text-gray-900 dark:text-white">
                Вход
            </h2>
        </div>

        <?= Alert::renderFromSession() ?>

        <form class="mt-8 space-y-6" action="/admin" method="POST">
            <div class="rounded-md space-y-4">
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
            </div>

            <div class="flex items-center justify-between">
                <?php Form::toggle('remember', 'Запомни ме', [
                    'value' => false
                ]); ?>

                <?= Link::make('/password/forgot', 'Забравена парола?'); ?>
            </div>

            <div>
                <?= Button::make('Вход в профила')->type('submit'); ?>
            </div>
        </form>

    </div>
</div>
