<?php

namespace Flex\Core\Traits;

use Flex\Models\Setting;
use Flex\Core\Routing\View;

trait HandlesSettings
{
    protected function getSettingsData(string $group, array $definedGroups, array $customData = []): array
    {
        if (!array_key_exists($group, $definedGroups)) {
            return [];
        }

        $groupData = $definedGroups[$group];
        $title = is_array($groupData) ? ($groupData['title'] ?? $group) : $groupData;

        $data = [
            'title' => 'Настройки: ' . $title,
            'currentGroup' => $group,
            'definedGroups' => $definedGroups,
            'group' => $group,
        ];

        return array_merge($data, $customData);
    }

    protected function updateSettings(string $group, array $postedSettings, string $redirectUrl)
    {
        $existingSettings = Setting::where('group', $group)
            ->whereIn('value', ['1', '0', 'true', 'false'])
            ->get();

        foreach ($existingSettings as $setting) {
            if (!isset($postedSettings[$setting->key])) {
                $this->saveSetting($setting->key, false, $group);
            }
        }

        foreach ($postedSettings as $key => $value) {
            $this->saveSetting($key, ($value === '1' ? true : $value), $group);
        }

        $_SESSION['flash_success'] = 'Настройките бяха записани успешно.';
        View::redirect($redirectUrl);
    }

    protected function saveSetting(string $key, $value, string $group)
    {
        $setting = Setting::firstOrNew(['key' => $key]);
        $type = is_bool($value) ? 'boolean' : 'string';

        $setting->fill(['key' => $key, 'value' => $value, 'group' => $group, 'type' => $type]);
        $setting->save();
    }

    public static function getGroupIcon(string $group): string
    {
        $groups = theme_info('settings_options.settings_page_groups', []);

        if (isset($groups[$group]['icon'])) {
            return $groups[$group]['icon'];
        }

        return 'fa-circle';
    }
}
