<?php

namespace Flex\Core\Controllers;

use Flex\Core\Traits\Updatable;
use Flex\Core\Routing\View;

class UpdateController extends BaseController
{
    use Updatable;

    public function index()
    {
        $latest = $this->fetchLatestVersionData();

        if (!$latest) {
            return $this->render(View::make('admin/update/index', ['error' => 'Неуспешна връзка със сървъра.']));
        }

        $needsUpdate = $this->checkVersionComparison($latest['latest_version']);

        return $this->render(View::make('admin/update/index', [
            'title' => 'Версия на системата',
            'latest' => $latest,
            'current' => $this->getVersionData(),
            'needsUpdate' => $needsUpdate
        ], 'admin'));
    }

    public function process()
    {
        $latest = $this->fetchLatestVersionData();

        $tempPath = base_path('storage/updates/tmp/update.zip');
        $extractPath = base_path();

        if (!$this->downloadUpdate($latest['download_url'], $tempPath)) {
            throw new \Exception("Грешка: Неуспешно изтегляне на файла.");
        }

        if (!$this->verifyIntegrity($tempPath, $latest['checksum'])) {
            throw new \Exception("Грешка: Невалидна контролна сума на архива.");
        }

        if (!$this->extractUpdate($tempPath, $extractPath)) {
            throw new \Exception("Грешка: Неуспешно разархивиране на файловете.");
        }

        $this->runPendingMigrations();

        @unlink($tempPath);

        View::redirect('/admin/update', 200);
    }
}
