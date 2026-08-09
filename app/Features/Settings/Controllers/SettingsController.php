<?php

declare(strict_types=1);

namespace Flex\Features\Settings\Controllers;

use Flex\Core\Helpers\Flash;
use Flex\Core\Http\RedirectResponse;
use Flex\Core\Http\Request;
use Flex\Core\View\Contracts\ViewRendererInterface;
use Flex\Core\View\ViewResponse;
use Flex\Features\Settings\Services\SettingsService;
use InvalidArgumentException;

final class SettingsController
{
    public function __construct(
        private readonly SettingsService $settings,
        private readonly ViewRendererInterface $views
    ) {
    }

    public function show(string $group): ViewResponse
    {
        $pageData = $this->settings->pageData($group);

        return $this->views->response(
            'Settings::show',
            $pageData->toArray(),
            'admin'
        );
    }

    public function update(
        Request $request,
        string $group
    ): RedirectResponse {
        $submittedSettings = $request->input(
            'settings',
            []
        );

        if (!is_array($submittedSettings)) {
            throw new InvalidArgumentException(
                'The settings payload must be an array.'
            );
        }

        $this->settings->updatePage(
            $group,
            $submittedSettings
        );

        Flash::success(
            'Настройките бяха записани успешно.'
        );

        return new RedirectResponse(
            '/admin/settings/' . rawurlencode($group)
        );
    }
}