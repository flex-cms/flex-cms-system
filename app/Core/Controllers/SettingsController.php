<?php

namespace Flex\Core\Controllers;

use Flex\Attributes\UseExceptions;
use Flex\Core\Controllers\BaseController;
use Flex\Core\Routing\View;
use Flex\Models\Setting;

class SettingsController extends BaseController
{
    private $languages = [
        'bg' => 'Български',
        'en' => 'English',
        'de' => 'Deutsch',
        'fr' => 'Français'
    ];

    private $timezones = [
        'Europe/Amsterdam' => 'Амстердам',
        'Europe/Athens' => 'Атина',
        'Europe/Belgrade' => 'Белград',
        'Europe/Berlin' => 'Берлин',
        'Europe/Brussels' => 'Брюксел',
        'Europe/Bucharest' => 'Букурещ',
        'Europe/Budapest' => 'Будапеща',
        'Europe/Dublin' => 'Дъблин',
        'Europe/Helsinki' => 'Хелзинки',
        'Europe/Istanbul' => 'Истанбул',
        'Europe/Kiev' => 'Киев',
        'Europe/Lisbon' => 'Лисабон',
        'Europe/London' => 'Лондон',
        'Europe/Madrid' => 'Мадрид',
        'Europe/Moscow' => 'Москва',
        'Europe/Paris' => 'Париж',
        'Europe/Prague' => 'Прага',
        'Europe/Rome' => 'Рим',
        'Europe/Sofia' => 'София',
        'Europe/Stockholm' => 'Стокхолм',
        'Europe/Vienna' => 'Виена',
        'Europe/Warsaw' => 'Варшава',
        'Europe/Zurich' => 'Цюрих'
    ];

    #[UseExceptions]
    public function show(string $group)
    {
        $definedGroups = $this->getDefinedGroups();

        if (!array_key_exists($group, $definedGroups)) {
            $_SESSION['flash_error'] = 'Невалидна група настройки.';
            View::redirect('/admin/settings');
        }

        $view = View::make('admin/settings/layout', [
            'title' => 'Настройки: ' . $definedGroups[$group],
            'currentGroup' => $group,
            'definedGroups' => $definedGroups,
            'group' => $group,
            'dateFormats' => $this->getDateFormats(),
            'languages' => $this->languages,
            'timezones' => $this->timezones
        ], 'admin');

        render_view($view);
    }

    #[UseExceptions]
    public function update(string $group)
    {
        $postedSettings = $_POST['settings'] ?? [];

        $existingSettings = Setting::where('group', $group)->whereIn('value', ['1', '0', true, false])->get();

        foreach ($existingSettings as $setting) {
            $key = $setting->key;

            if (!isset($postedSettings[$key])) {
                $this->saveSetting($key, false, $group);
            }
        }

        foreach ($postedSettings as $key => $value) {
            $this->saveSetting($key, ($value === '1' ? true : $value), $group);
        }

        $_SESSION['flash_success'] = 'Настройките бяха записани успешно.';
        View::redirect('/admin/settings/' . $group);
    }

    public static function getGroupIcon(string $group): string
    {
        $icons = [
            'general' => 'fa-cog',
            'mail' => 'fa-envelope',
            'system' => 'fa-server',
            'security' => 'fa-shield-alt'
        ];
        return $icons[$group] ?? 'fa-circle';
    }

    #[UseExceptions]
    private function saveSetting(string $key, $value, string $group)
    {
        $setting = Setting::firstOrNew(['key' => $key]);

        $type = is_bool($value) ? 'boolean' : 'string';

        $setting->fill([
            'key' => $key,
            'value' => $value,
            'group' => $group,
            'type' => $type
        ]);

        $setting->save();
    }

    private function getDateFormats(): array
    {
        return [
            'd.m.Y' => 'Ден.Месец.Година (31.12.2025)',
            'd/m/Y' => 'Ден/Месец/Година (31/12/2025)',
            'Y-m-d' => 'Година-Месец-Ден (ISO 2025-12-31)',
            'd M Y' => 'Ден Месец Година (31 Дек 2025)',
            'm/d/Y' => 'Месец/Ден/Година (САЩ формат)',
            'l, j F Y' => 'Ден от седмицата, Ден Месец Година (Сряда, 31 Декември 2025)'
        ];
    }

    protected function getDefinedGroups(): array
    {
        return [
            'general' => 'Общи настройки',
            'mail' => 'Имейл сървър',
            'system' => 'Системни параметри',
            'security' => 'Сигурност'
        ];
    }
}