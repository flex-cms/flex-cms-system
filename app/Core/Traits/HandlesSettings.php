<?php

namespace Flex\Core\Traits;

use Flex\Models\Setting;
use Flex\Core\Routing\View;
use Flex\Core\Services\MediaManager;

trait HandlesSettings
{
    protected ?MediaManager $mediaManager = null;

    public function __construct()
    {
        $this->mediaManager = new MediaManager();
    }

    protected function updateSettings(string $group, array $postedSettings, string $redirectUrl)
    {
        $uploadedFiles = $this->handleFileUploads();
        $postedSettings = array_merge($postedSettings, $uploadedFiles);

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

    protected function handleFileUploads(): array
    {
        $uploadedPaths = [];

        if (!$this->mediaManager) {
            $this->mediaManager = new MediaManager();
        }

        foreach ($_FILES as $fieldKey => $fileData) {
            if (isset($fileData['error']) && $fileData['error'] === UPLOAD_ERR_OK) {
                
                $uploadedPath = $this->mediaManager->upload($fileData, ACTIVE_THEME);

                if ($uploadedPath) {
                    $uploadedPaths[$fieldKey] = $uploadedPath;
                }
            }
        }

        return $uploadedPaths;
    }

    protected function saveSetting(string $key, $value, string $group)
    {
        $setting = Setting::firstOrNew(['key' => $key]);
        $type = is_bool($value) ? 'boolean' : 'string';

        $setting->fill(['key' => $key, 'value' => $value, 'group' => $group, 'type' => $type]);
        $setting->save();
    }
}
