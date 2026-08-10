<?php

declare(strict_types=1);

namespace Flex\Features\Settings\Controllers;

use Flex\Core\Helpers\Flash;
use Flex\Core\Http\JsonResponse;
use Flex\Core\Http\RedirectResponse;
use Flex\Core\Http\Request;
use Flex\Core\View\ViewResponse;
use Flex\Features\AdminUI\Services\AdminUIRenderer;
use Flex\Features\Settings\Services\SettingsService;
use Flex\Core\Assets\AdminAssetRegistry;
use InvalidArgumentException;

final class SettingsController
{
    public function __construct(
        private readonly SettingsService $settings,
        private readonly AdminUIRenderer $adminUI,
        private readonly AdminAssetRegistry $assets
    ) {
    }

    public function show(
        string $group
    ): ViewResponse {
        $pageData =
            $this->settings->pageData(
                $group
            );

        return $this->adminUI->response(
            'Settings::groups/' . $group,
            $pageData->toArray()
        );
    }

    public function update(Request $request, string $group): JsonResponse {
        $submittedSettings = $request->input(
            'settings',
            []
        );

        if (!is_array($submittedSettings)) {
            return new JsonResponse(
                [
                    'success' => false,
                    'message' => 'Невалидни данни за настройките.',
                ],
                422
            );
        }

        $this->settings->updatePage(
            $group,
            $submittedSettings
        );

        return new JsonResponse([
            'success' => true,
            'message' => 'Настройките бяха записани успешно.',
            'group' => $group,
        ]);
    }
}
