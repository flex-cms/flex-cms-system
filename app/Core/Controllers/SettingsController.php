<?php

namespace Flex\Core\Controllers;

use DateTimeZone;
use Flex\Attributes\UseExceptions;
use Flex\Core\Controllers\BaseController;
use Flex\Core\Traits\HandlesSettings;

class SettingsController extends BaseController
{
    use HandlesSettings;

    private array $languages;
    private array $timezones;
    private array $dateFormats;

    public function __construct()
    {
        $this->languages = core_info('languages');
        $this->timezones = $this->getTimezones();
        $this->dateFormats = core_info('date_formats');
    }

    private function getTimezones(): array
    {
        $timezones = [];

        foreach (DateTimeZone::listIdentifiers() as $timezone) {
            $timezones[$timezone] = str_replace('_', ' ', basename($timezone));
        }

        asort($timezones, SORT_NATURAL | SORT_FLAG_CASE);

        return $timezones;
    }

    #[UseExceptions]
    public function show(string $group)
    {
        $groups = core_info('settings_options.settings_page_groups', []);

        $data = $this->getSettingsData($group, (array) $groups, [
            'languages'   => $this->languages,
            'timezones'   => $this->timezones,
            'dateFormats' => $this->dateFormats,
        ]);

        render_view('admin/settings/layout', $data, 'core', 'admin');
    }

    #[UseExceptions]
    public function update(string $group)
    {
        $this->updateSettings('system', $_POST['settings'] ?? [], '/admin/settings/' . $group);
    }

    private function getSettingsData(string $group, array $definedGroups, array $customData = []): array
    {
        if (!array_key_exists($group, $definedGroups)) {
            return [];
        }

        $groupData = $definedGroups[$group];
        $label = is_array($groupData) ? ($groupData['label'] ?? $group) : $groupData;

        $data = [
            'title' => 'Настройки: ' . $label,
            'currentGroup' => $group,
            'definedGroups' => $definedGroups ?? [],
            'group' => $group,
        ];

        return array_merge($data, $customData);
    }
    
    public static function getGroupIcon(string $group): string|null
    {
        $groups = core_info('settings_options.settings_page_groups', []);

        if (isset($groups[$group]['icon'])) {
            return $groups[$group]['icon'];
        }

        return null;
    }
}