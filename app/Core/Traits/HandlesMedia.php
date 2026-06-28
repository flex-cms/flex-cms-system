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
    
    public function handleFileUploads(array $currentOptions, string $folder = 'uploads'): array
    {
        $manager = $this->getMediaManager();
        $options = $currentOptions;

        if (!empty($_FILES)) {
            foreach ($_FILES as $key => $file) {
                if (isset($file['error']) && $file['error'] === UPLOAD_ERR_OK) {

                    if (!empty($options[$key])) {
                        $manager->remove($options[$key]);
                    }

                    $path = $manager->upload($file, $folder);
                    if ($path) {
                        $options[$key] = $path;
                    }
                }
            }
        }
        return $options;
    }
}
