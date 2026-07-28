<?php

namespace Flex\Core\Traits;

use Flex\Core\Services\MediaManager;

trait HandlesMedia
{
    protected ?MediaManager $mediaManager = null;

    protected function getMediaManager(): MediaManager
    {
        if ($this->mediaManager === null) {
            $this->mediaManager = new MediaManager();
        }

        return $this->mediaManager;
    }

    public function handleFileUploads(
        array $currentOptions,
        string $folder = 'uploads',
        ?array $allowedKeys = null
    ): array {
        $manager = $this->getMediaManager();
        $options = $currentOptions;

        foreach ($_POST as $key => $value) {
            if (!str_ends_with($key, '_remove') || (string) $value !== '1') {
                continue;
            }

            $fileKey = substr($key, 0, -7);

            if ($allowedKeys !== null && !in_array($fileKey, $allowedKeys, true)) {
                continue;
            }

            if (!empty($options[$fileKey])) {
                $manager->remove($options[$fileKey]);
            }

            unset($options[$fileKey]);
        }

        foreach ($_FILES as $key => $file) {
            if ($allowedKeys !== null && !in_array($key, $allowedKeys, true)) {
                continue;
            }

            if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
                continue;
            }

            $oldPath = $options[$key] ?? null;
            $newPath = $manager->upload($file, $folder);

            if (!$newPath) {
                continue;
            }

            $options[$key] = $newPath;

            if ($oldPath && $oldPath !== $newPath) {
                $manager->remove($oldPath);
            }
        }

        return $options;
    }
}
