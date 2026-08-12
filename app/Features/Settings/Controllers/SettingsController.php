<?php

declare(strict_types=1);

namespace Flex\Features\Settings\Controllers;

use Flex\Core\Http\JsonResponse;
use Flex\Core\Http\Request;
use Flex\Core\View\ViewResponse;
use Flex\Features\AdminUI\Services\AdminUIRenderer;
use Flex\Features\Settings\Services\SettingsService;

final class SettingsController
{
    public function __construct(
        private readonly SettingsService $settings,
        private readonly AdminUIRenderer $adminUI
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

    public function dateRuntimeConfig(): JsonResponse
    {
        return new JsonResponse(
            $this->settings->dateRuntimeConfig()
        );
    }
}
