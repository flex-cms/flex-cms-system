<?php

namespace Flex\Core\Services;

use Flex\Core\Helpers\Flash;
use Flex\Models\Setting;

class MediaManager
{
    protected string $baseDir = 'uploads/';

    public function upload(array $file, string $folder = 'general'): string|bool
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        if (!$this->validateFile($file)) {
            return false;
        }

        $fileName = $this->generateFileName($file);
        $path = $this->getUploadPath($folder);

        if (move_uploaded_file($file['tmp_name'], $path . $fileName)) {
            return '/' . $path . $fileName;
        }

        return false;
    }

    private function validateFile(array $file): bool
    {
        $maxSize = (int)Setting::get('media_max_size', 5) * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            Flash::error('Максималният размер на файла е ' . (Setting::get('media_max_size', 5)) . 'MB.');
            return false;
        }

        $allowed = explode(',', str_replace(' ', '', Setting::get('media_allowed_extensions', 'jpg,png,webp')));
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            Flash::error('Невалиден формат. Разрешени: ' . implode(', ', $allowed));
            return false;
        }

        return true;
    }

    private function generateFileName(array $file): string
    {
        if (Setting::get('media_keep_original_name', false)) {
            return preg_replace('/[^a-zA-Z0-9\._-]/', '_', $file['name']);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        return uniqid('img_', true) . '.' . $extension;
    }

    private function getUploadPath(string $folder): string
    {
        $subPath = Setting::get('media_use_date_folders', true) ? date('Y/m/d') : '';
        $path = $this->baseDir . $folder . ($subPath ? '/' . $subPath : '') . '/';

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        return $path;
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