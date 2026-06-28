<?php

use Flex\Core\Helpers\DateHelper;
use Flex\Core\UI\Form;
use Flex\Models\Setting;

$currentUrl = base_url();

$languages = $languages ?? [];
$timezones = $timezones ?? [];
$dateFormats = $dateFormats ?? [];
?>

<?php Form::section(function () use ($currentUrl) { ?>
    <?php Form::row(function () use ($currentUrl) {

        Form::input('settings[site_name]', 'Име на сайта', [
            'value' => Setting::get('site_name', 'Flex CMS'),
            'required' => true
        ]);

        Form::input('settings[admin_email]', 'Административен имейл', [
            'value' => Setting::get('admin_email', ''),
            'type' => 'email',
            'required' => true
        ]);

        Form::input('settings[site_url]', 'URL адрес', [
            'value' => Setting::get('site_url', $currentUrl),
            'placeholder' => 'https://example.com'
        ]);

    }, ['md' => 2, 'lg' => 3]); ?>

    <?php Form::textarea('settings[site_description]', 'Кратко описание (Meta Description)', [
        'value' => Setting::get('site_description', ''),
        'rows' => 5
    ]); ?>
<?php }, 'Основни настройки'); ?>

<?php Form::section(function () use ($timezones, $dateFormats) { ?>
    <?php Form::row(function () use ($timezones, $dateFormats) {
        Form::customSelect(
            'settings[timezone]',
            'Часова зона',
            $timezones,
            Setting::get('timezone', 'Europe/Sofia')
        );
        Form::customSelect(
            'settings[date_format]',
            'Формат на датата',
            $dateFormats,
            Setting::get('date_format', 'd.m.Y')
        );
    }, ['md' => 2]); ?>

    <div class="mt-2 p-3 bg-slate-50 dark:bg-slate-900/30 rounded border border-slate-200 dark:border-slate-700 text-sm">
        <span class="text-slate-500 dark:text-slate-400">Текущо системно време: </span>
        <span class="font-mono font-medium text-slate-800 dark:text-slate-200">
            <?php echo DateHelper::format(); ?>
        </span>
    </div>

    <div class="mt-4">
        <?php Form::toggle('settings[debug_mode]', 'Debug режим', [
            'value' => (bool) Setting::get('debug_mode', false),
            'description' => 'Използвай само за разработка. При продукция винаги трябва да е изключен.'
        ]); ?>
    </div>
<?php }, 'Допълнителни параметри'); ?>

<?php Form::section(function () use ($languages) { ?>
    <div x-data="{ multipleLanguages: <?= (bool) Setting::get('enable_multilang', false) ? 'true' : 'false' ?> }">

        <div class="mb-5">
            <?php Form::toggle('settings[enable_multilang]', 'Активиране на многоезичност', [
                'value' => (bool) Setting::get('enable_multilang', false),
                'description' => 'Ако е активирано, сайтът ще поддържа множество езици едновременно.',
                'attr' => [
                    'name' => 'settings[enable_multilang]',
                    '@change' => 'multipleLanguages = $event.target.checked'
                ]
            ]); ?>
        </div>

        <div x-show="multipleLanguages" x-collapse x-cloak>
            <div class="p-5 mb-5 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-200 dark:border-slate-600">
                <?php Form::row(function () use ($languages) {
                    Form::customSelect(
                        'settings[site_default_lang]',
                        'Език на сайта по подразбиране',
                        $languages,
                        Setting::get('site_default_lang', 'bg')
                    );
                }, 2); ?>
            </div>
        </div>

        <?php Form::row(function () use ($languages) {
            Form::customSelect(
                'settings[admin_default_lang]',
                'Език на админ панела',
                $languages,
                Setting::get('admin_default_lang', 'bg')
            );
        }, 1); ?>
    </div>
<?php }, 'Езикови настройки'); ?>
