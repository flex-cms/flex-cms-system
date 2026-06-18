<?php

use Flex\Core\UI\Form;

Form::create(['action' => '/install/process-db', 'method' => 'POST']);
?>

<div class="space-y-5">
    <h2 class="text-xl font-semibold mb-4">Настройка на базата данни</h2>
    <p class="mb-5">Моля, въведете данните за връзка с Вашата MySQL база данни.</p>

    <?php
    Form::input('db_host', 'Хост', [
        'value' => 'localhost',
        'required' => true,
        'placeholder' => 'Напр. localhost',
        'hint' => 'Адресът на сървъра на базата данни. Обикновено е localhost.'
    ]);

    Form::input('db_name', 'Име на базата данни', [
        'required' => true,
        'placeholder' => 'flex_cms_db',
        'hint' => 'Името на празната база данни, която сте създали за системата.'
    ]);

    Form::input('db_user', 'Потребител', [
        'required' => true,
        'placeholder' => 'root',
        'hint' => 'Потребителско име с права за достъп до базата данни.'
    ]);

    Form::input('db_pass', 'Парола', [
        'type' => 'password',
        'placeholder' => '********',
        'hint' => 'Паролата за потребителя на базата данни. Оставете празно, ако няма парола.'
    ]);

    Form::input('admin_email', 'Администраторски имейл', [
        'type' => 'email',
        'required' => true,
        'placeholder' => 'admin@example.com',
        'hint' => 'Използвайте този имейл за вход в контролния панел.'
    ]);

    Form::input('admin_pass', 'Администраторска парола', [
        'type' => 'password',
        'required' => true,
        'placeholder' => '********',
        'hint' => 'Изберете сигурна парола с поне 8 символа.'
    ]);
    ?>

    <div class="pt-4">
        <?php Form::submit('Инсталирай сега', 'fa-download'); ?>
    </div>
</div>

<?php Form::close(); ?>
