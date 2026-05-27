<?php

use Flex\Core\UI\Form;
use Flex\Models\Setting;

$encryptionOptions = [
    'tls'  => 'TLS',
    'ssl'  => 'SSL',
    'none' => 'Без криптиране'
];
?>

<?php Form::section(function () use ($encryptionOptions) { ?>
    <?php Form::row(function () {
        Form::input('settings[smtp_host]', 'SMTP Хост', [
            'value' => Setting::get('smtp_host', ''),
            'placeholder' => 'mail.example.com'
        ]);
        Form::input('settings[smtp_port]', 'SMTP Порт', [
            'value' => Setting::get('smtp_port', '587'),
            'type' => 'number'
        ]);
    }, 2); ?>

    <?php Form::row(function () {
        Form::input('settings[smtp_user]', 'SMTP Потребител', [
            'value' => Setting::get('smtp_user', ''),
            'placeholder' => 'user@example.com'
        ]);
        Form::input('settings[smtp_pass]', 'SMTP Парола', [
            'value' => Setting::get('smtp_pass', ''),
            'type' => 'password'
        ]);
    }, 2); ?>

    <?php Form::row(function () use ($encryptionOptions) {
        Form::customSelect(
            'settings[smtp_encryption]',
            'Криптиране',
            $encryptionOptions,
            Setting::get('smtp_encryption', 'tls')
        );

        Form::input('settings[from_email]', 'Имейл за "От"', [
            'value' => Setting::get('from_email', ''),
            'placeholder' => 'noreply@example.com',
        ]);
    }, 2); ?>
    
    <h2 class="text-lg text-primary dark:text-indigo-200 font-semibold">Тестване на връзката</h2>
    <p class="text-primary dark:text-indigo-300 mb-5">
        Изпратете тестов имейл, за да се уверите, че настройките са коректни.
    </p>
    <button type="button" class="px-4 py-2 bg-primary text-white rounded-md hover:bg-primary transition">
        Изпращане на тестов имейл
    </button>

<?php }, 'SMTP Конфигурация'); ?>
