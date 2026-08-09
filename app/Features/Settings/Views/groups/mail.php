<?php

declare(strict_types=1);

use Flex\Core\UI\Form;

$settings = $settings ?? [];

$value = static function (
    string $key,
    mixed $default = null
) use ($settings): mixed {
    return array_key_exists($key, $settings)
        ? $settings[$key]
        : $default;
};

$encryptionOptions = [
    'tls' => 'TLS',
    'ssl' => 'SSL',
    'none' => 'Без криптиране',
];
?>

<?php Form::section(function () use (
    $value,
    $encryptionOptions
): void { ?>
    <?php Form::row(function () use ($value): void {
        Form::input(
            'settings[smtp_host]',
            'SMTP Хост',
            [
                'value' => $value('smtp_host', ''),
                'placeholder' => 'mail.example.com',
            ]
        );

        Form::input(
            'settings[smtp_port]',
            'SMTP Порт',
            [
                'value' => $value('smtp_port', 587),
                'type' => 'number',
            ]
        );

        Form::input(
            'settings[smtp_user]',
            'SMTP Потребител',
            [
                'value' => $value('smtp_user', ''),
                'placeholder' => 'user@example.com',
            ]
        );
    }, [
        'md' => 2,
        'lg' => 3,
    ]); ?>

    <?php Form::row(function () use (
        $value,
        $encryptionOptions
    ): void {
        Form::input(
            'settings[smtp_pass]',
            'SMTP Парола',
            [
                /*
                 * Никога не връщаме записаната SMTP
                 * парола обратно в HTML документа.
                 *
                 * Празно поле означава:
                 * запази текущата парола.
                 */
                'value' => '',
                'type' => 'password',
                'placeholder' =>
                    'Остави празно, за да запазиш текущата',
                'autocomplete' => 'new-password',
            ]
        );

        Form::customSelect(
            'settings[smtp_encryption]',
            'Криптиране',
            $encryptionOptions,
            $value('smtp_encryption', 'tls')
        );

        Form::input(
            'settings[from_email]',
            'Изпращане от',
            [
                'value' => $value('from_email', ''),
                'placeholder' => 'noreply@example.com',
                'type' => 'email',
            ]
        );
    }, [
        'md' => 2,
        'lg' => 3,
    ]); ?>
<?php }, 'SMTP Конфигурация'); ?>