<?php

namespace Flex\Core\Services;

class MediaManager
{
    protected string $baseDir = 'uploads/';

    public function upload(array $file, string $folder = 'general'): ?string
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('img_', true) . '.' . $extension;

        $path = $this->baseDir . $folder . '/';

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $path . $fileName)) {
            return '/' . $path . $fileName;
        }

        return null;
    }

    public function remove(string $filePath): bool
    {
        $fullPath = ltrim($filePath, '/');

        if (file_exists($fullPath)) {
            return unlink($fullPath);
        }

        return false;
    }
}
