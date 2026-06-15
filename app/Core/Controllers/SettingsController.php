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

        $groupData = $definedGroups[$group];
        $title = is_array($groupData) ? ($groupData['title'] ?? $group) : $groupData;

        View::render('admin/settings/layout', [
            'title' => 'Настройки: ' . $title,
            'currentGroup' => $group,
            'definedGroups' => $definedGroups,
            'group' => $group,
            'dateFormats' => $this->getDateFormats(),
            'languages' => $this->languages,
            'timezones' => $this->timezones
        ], 'core', 'admin');
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
        $groups = theme_info('settings_options.settings_page_groups', []);

        if (isset($groups[$group]['icon'])) {
            return $groups[$group]['icon'];
        }

        return 'fa-circle';
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
        $formats = theme_info('settings_options.date_formats');
        return $formats;
    }

    protected function getDefinedGroups(): array
    {
        $dynamicGroups = theme_info('settings_options.settings_page_groups', []);
        return (array) $dynamicGroups;
    }
}
