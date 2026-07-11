<?php

use Flex\Core\UI\Components\Alert;
use Flex\Core\UI\Components\Button;
use Flex\Core\UI\Components\InputField;
use Flex\Core\UI\Form;
?>

<div class="flex items-center justify-center mt-5 md:mt-10 py-5 md:py-10 px-4 sm:px-6 lg:px-8">
    <div
        class="max-w-md w-full space-y-8 bg-white dark:bg-gray-800 p-5 md:p-10 max-sm:text-sm rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700">

        <div class="text-center">
            <?php Form::heading('Нова парола', 'h2', 'text-2xl font-semibold text-gray-900 dark:text-white'); ?>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Моля, въведете и потвърдете вашата нова парола по-долу.
            </p>
        </div>

        <?= Alert::renderFromSession() ?>

        <?php Form::create([
            'action' => '/password/reset',
            'method' => 'POST'
        ]); ?>

        <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token'] ?? '') ?>">

        <div class="rounded-md space-y-4">
            <?= InputField::make('password', 'Нова парола')
                ->type('password')
                ->placeholder('Въведете новата си парола...')
                ->required();
            ?>

            <?= InputField::make('password_confirmation', 'Потвърдете новата парола')
                ->type('password')
                ->placeholder('Въведете новата си парола отново...')
                ->required();
            ?>
        </div>

        <div>
            <?= Button::make('Обнови паролата')
                ->type('submit')
                ->loading('isSubmitting', 'Моля, изчакайте...')
                ->addClasses('w-full');
            ?>
        </div>

        <?php Form::close(); ?>

    </div>
</div>