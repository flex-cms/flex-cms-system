<?php

namespace Flex\Core\Controllers;

use Flex\Attributes\UseExceptions;
use Flex\Core\Controllers\BaseController;
use Flex\Core\Routing\View;
use Flex\Core\Traits\HandlesSettings;

class SettingsController extends BaseController
{
    use HandlesSettings;

    private $languages;
    private $timezones;
    private $dateFormats;

    public function __construct()
    {
        $this->languages = core_info('languages');
        $this->timezones = core_info('timezones');
        $this->dateFormats = core_info('date_formats');
    }

    #[UseExceptions]
    public function show(string $group)
    {
        $groups = theme_info('settings_options.settings_page_groups', []);

        $data = $this->getSettingsData($group, (array) $groups, [
            'languages' => core_info('languages'),
            'timezones' => core_info('timezones'),
            'dateFormats' => core_info('date_formats')
        ]);

        render_view('admin/settings/layout', $data, 'core', 'admin');
    }

    #[UseExceptions]
    public function update(string $group)
    {
        $this->updateSettings($group, $_POST['settings'] ?? [], '/admin/settings/' . $group);
    }
}