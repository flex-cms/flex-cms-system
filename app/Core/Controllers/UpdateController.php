<?php

namespace Flex\Core\Controllers;

use Flex\Attributes\UseExceptions;
use Flex\Core\Traits\Updatable;
use Flex\Core\Routing\View;

class UpdateController extends BaseController
{
    use Updatable;

    #[UseExceptions]
    public function index()
    {
        $latest = $this->fetchLatestVersionData();

        if (!$latest) {
            $_SESSION['flush_error'] = 'Неуспешна връзка със сървъра.';
            render_view('admin/update/index');
        }

        $needsUpdate = $this->checkVersionComparison($latest['latest_version']);

        $data = [
            'title' => 'Версия на системата',
            'latest' => $latest,
            'current' => $this->getVersionData(),
            'needsUpdate' => $needsUpdate,
        ];

        render_view('admin/update/index', $data);
    }

    #[UseExceptions]
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
