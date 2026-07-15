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
            'value' => Setting::getValue('smtp_host', ''),
            'placeholder' => 'mail.example.com'
        ]);
        
        Form::input('settings[smtp_port]', 'SMTP Порт', [
            'value' => Setting::getValue('smtp_port', '587'),
            'type' => 'number'
        ]);
        
        Form::input('settings[smtp_user]', 'SMTP Потребител', [
            'value' => Setting::getValue('smtp_user', ''),
            'placeholder' => 'user@example.com'
        ]);

    }, ['md' => 2, 'lg' => 3]); ?>

    <?php Form::row(function () use ($encryptionOptions) {
        
        Form::input('settings[smtp_pass]', 'SMTP Парола', [
            'value' => Setting::getValue('smtp_pass', ''),
            'type' => 'password'
        ]);

        Form::customSelect(
            'settings[smtp_encryption]',
            'Криптиране',
            $encryptionOptions,
            Setting::getValue('smtp_encryption', 'tls')
        );

        Form::input('settings[from_email]', 'Изпращане от', [
            'value' => Setting::getValue('from_email', ''),
            'placeholder' => 'noreply@example.com',
        ]);

    }, ['md' => 2, 'lg' => 3]); ?>

<?php }, 'SMTP Конфигурация'); ?>
