<?php

declare(strict_types=1);

namespace Flex\Features\AdminUI\Controllers;

use Flex\Core\View\ViewResponse;
use Flex\Features\AdminUI\Services\AdminUIRenderer;

final readonly class PreviewController
{
    public function __construct(
        private AdminUIRenderer $adminUI
    ) {
    }

    public function show(): ViewResponse
    {
        return $this->adminUI->response(
            'AdminUI::preview',
            [
                'title' => 'Нов Flex Admin UI',

                'description' =>
                    'Изолиран преглед на новата '
                    . 'Lit и Turbo архитектура.',

                'primaryButton' => [
                    'label' => 'Текущ админ панел',
                    'url' => '/admin',
                    'icon' => 'fa-arrow-left',
                ],
            ]
        );
    }
}