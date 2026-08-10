<?php

declare(strict_types=1);

namespace Flex\Features\AdminUI\Services;

use Flex\Core\View\Contracts\ViewRendererInterface;
use Flex\Core\View\ViewResponse;
use Flex\Features\AdminUI\Configuration\AdminUIConfig;
use Flex\Features\AdminUI\Navigation\SidebarRegistry;

final readonly class AdminUIRenderer
{
    public const LAYOUT = 'AdminUI::admin';

    public function __construct(
        private ViewRendererInterface $views,
        private AdminUIAssets $assets,
        private AdminUIConfig $config,
        private SidebarRegistry $sidebars
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(
        string $view,
        array $data = []
    ): string {
        return $this->views->render(
            $view,
            $this->withAdminUIData($data),
            self::LAYOUT
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public function response(
        string $view,
        array $data = [],
        int $status = 200
    ): ViewResponse {
        return $this->views->response(
            $view,
            $this->withAdminUIData($data),
            self::LAYOUT,
            $status
        );
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function withAdminUIData(
        array $data
    ): array {
        $navigationContext =
            $data['navigationContext'] ?? null;

        $primarySidebar = $this->sidebars
            ->sidebar(
                SidebarRegistry::DEFAULT_SIDEBAR
            )
            ->toArray($navigationContext);

        return array_replace(
            $data,
            [
                'adminUIAssets' => $this->assets,

                'adminUIConfig' => [
                    'name' =>
                        $this->config->name(),

                    'defaultTheme' =>
                        $this->config
                            ->defaultTheme(),

                    'turboEnabled' =>
                        $this->config
                            ->turboEnabled(),

                    'turboPaths' =>
                        $this->config
                            ->turboPaths(),
                ],

                'adminUISidebar' =>
                    $primarySidebar,

                'adminUISidebars' =>
                    $this->sidebars->toArray(
                        $navigationContext
                    ),
            ]
        );
    }
}
