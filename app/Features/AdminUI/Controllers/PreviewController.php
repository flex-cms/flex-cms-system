<?php

declare(strict_types=1);

namespace Flex\Features\AdminUI\Controllers;

use Flex\Core\Http\Request;
use Flex\Core\View\ViewResponse;
use Flex\Features\AdminUI\Services\AdminUIRenderer;

final readonly class PreviewController
{
    public function __construct(
        private AdminUIRenderer $adminUI
    ) {
    }

    public function show(
        Request $request
    ): ViewResponse {
        $allowedTabs = [
            'overview',
            'components',
            'navigation',
        ];

        $activeTab = $request->string(
            'tab',
            'overview'
        );

        if (
            !in_array(
                $activeTab,
                $allowedTabs,
                true
            )
        ) {
            $activeTab = 'overview';
        }

        return $this->adminUI->response(
            'AdminUI::preview',
            [
                'title' => 'Нов Flex Admin UI',

                'description' =>
                    'Изолиран преглед на новата '
                    . 'Lit и Turbo архитектура.',

                'activeTab' => $activeTab,

                'primaryButton' => [
                    'label' =>
                        'Текущ админ панел',

                    'url' => '/admin',

                    'icon' => 'fa-arrow-left',
                ],
            ]
        );
    }
}
