<?php

declare(strict_types=1);

use Carbon\Carbon;
use Flex\Core\UI\Form;

$settings = $settings ?? [];
$languages = $languages ?? [];
$timezones = $timezones ?? [];
$dateFormats = $dateFormats ?? [];

$value = static function (
    string $key,
    mixed $default = null
) use ($settings): mixed {
    return array_key_exists($key, $settings)
        ? $settings[$key]
        : $default;
};

$currentUrl = base_url();

$timezone = (string) $value(
    'timezone',
    'Europe/Sofia'
);

$dateFormat = (string) $value(
    'date_format',
    'd.m.Y'
);

$adminLanguage = (string) $value(
    'admin_default_lang',
    'bg'
);

try {
    $currentTime = Carbon::now($timezone)
        ->locale($adminLanguage)
        ->translatedFormat($dateFormat . ' : H:i');
} catch (Throwable) {
    $currentTime = Carbon::now('Europe/Sofia')
        ->locale('bg')
        ->translatedFormat('d.m.Y : H:i');
}
?>

<?php Form::section(function () use (
    $value,
    $currentUrl
): void { ?>
    <?php Form::row(function () use (
        $value,
        $currentUrl
    ): void {
        Form::input(
            'settings[site_name]',
            'Име на сайта',
            [
                'value' => $value(
                    'site_name',
                    'Flex CMS'
                ),
                'required' => true,
            ]
        );

        Form::input(
            'settings[admin_email]',
            'Административен имейл',
            [
                'value' => $value('admin_email', ''),
                'type' => 'email',
                'required' => true,
            ]
        );

        Form::input(
            'settings[site_url]',
            'URL адрес',
            [
                'value' => $value(
                    'site_url',
                    $currentUrl
                ),
                'placeholder' => 'https://example.com',
            ]
        );
    }, [
        'md' => 2,
        'lg' => 3,
    ]); ?>

    <?php Form::textarea(
        'settings[site_description]',
        'Кратко описание (Meta Description)',
        [
            'value' => $value(
                'site_description',
                ''
            ),
            'rows' => 5,
        ]
    ); ?>
<?php }, 'Основни настройки'); ?>

<?php Form::section(function () use (
    $value,
    $timezones,
    $dateFormats,
    $currentTime
): void { ?>
    <?php Form::row(function () use (
        $value,
        $timezones,
        $dateFormats
    ): void {
        Form::customSelect(
            'settings[timezone]',
            'Часова зона',
            $timezones,
            $value('timezone', 'Europe/Sofia')
        );

        Form::customSelect(
            'settings[date_format]',
            'Формат на датата',
            $dateFormats,
            $value('date_format', 'd.m.Y')
        );
    }, ['md' => 2]); ?>

    <div
        class="mt-2 rounded border border-slate-200 bg-slate-50 p-3 text-sm dark:border-slate-700 dark:bg-slate-900/30"
    >
        <span class="text-slate-500 dark:text-slate-400">
            Текущо системно време:
        </span>

        <span class="font-mono font-medium text-slate-800 dark:text-slate-200">
            <?= htmlspecialchars(
                $currentTime,
                ENT_QUOTES,
                'UTF-8'
            ) ?>
        </span>
    </div>

    <div class="mt-4">
        <?php Form::toggle(
            'settings[debug_mode]',
            'Debug режим',
            [
                'value' => (bool) $value(
                    'debug_mode',
                    false
                ),
                'description' =>
                    'Използвай само за разработка. '
                    . 'При продукция винаги трябва '
                    . 'да е изключен.',
            ]
        ); ?>
    </div>
<?php }, 'Допълнителни параметри'); ?>

<?php Form::section(function () use (
    $value,
    $languages
): void { ?>
    <div
        x-data="{
            multipleLanguages:
                <?= (bool) $value(
                    'enable_multilang',
                    false
                ) ? 'true' : 'false' ?>
        }"
    >
        <div class="mb-5">
            <?php Form::toggle(
                'settings[enable_multilang]',
                'Активиране на многоезичност',
                [
                    'value' => (bool) $value(
                        'enable_multilang',
                        false
                    ),
                    'description' =>
                        'Ако е активирано, сайтът ще '
                        . 'поддържа множество езици '
                        . 'едновременно.',
                    'attr' => [
                        '@change' =>
                            'multipleLanguages = '
                            . '$event.target.checked',
                    ],
                ]
            ); ?>
        </div>

        <div x-show="multipleLanguages" x-collapse x-cloak>
            <div
                class="mb-5 rounded-lg border border-slate-200 bg-slate-50 p-5 dark:border-slate-600 dark:bg-slate-800/50"
            >
                <?php Form::row(function () use (
                    $value,
                    $languages
                ): void {
                    Form::customSelect(
                        'settings[site_default_lang]',
                        'Език на сайта по подразбиране',
                        $languages,
                        $value(
                            'site_default_lang',
                            'bg'
                        )
                    );
                }, 2); ?>
            </div>
        </div>

        <?php Form::row(function () use (
            $value,
            $languages
        ): void {
            Form::customSelect(
                'settings[admin_default_lang]',
                'Език на админ панела',
                $languages,
                $value(
                    'admin_default_lang',
                    'bg'
                )
            );
        }, 1); ?>
    </div>
<?php }, 'Езикови настройки'); ?>