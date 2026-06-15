<?php

use Flex\Core\UI\Form;

Form::create(['action' => '/install/process-db', 'method' => 'POST']);
?>

<div class="space-y-6">
    <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Настройка на базата данни</h2>
    <p class="text-gray-600 mb-6 dark:text-gray-400">Моля, въведете данните за връзка с вашата MySQL база данни.</p>

    <?php
    Form::input('db_host', 'Хост', [
        'value' => 'localhost',
        'required' => true,
        'placeholder' => 'Напр. localhost'
    ]);

    Form::input('db_name', 'Име на базата данни', [
        'required' => true,
        'placeholder' => 'flex_cms_db'
    ]);

    Form::input('db_user', 'Потребител', [
        'required' => true,
        'placeholder' => 'root'
    ]);

    Form::input('db_pass', 'Парола', [
        'type' => 'password',
        'placeholder' => '********'
    ]);
    ?>

    <div class="pt-4">
        <?php Form::submit('Инсталирай сега', 'fa-download'); ?>
    </div>
</div>

<?php Form::close(); ?>