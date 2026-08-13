<?php

declare(strict_types=1);

namespace Tests\Unit\Features\AdminUI\Services;

use Flex\Core\Assets\AdminAssetRegistry;
use Flex\Core\Assets\ViteAssetResolver;
use Flex\Core\View\Contracts\ViewRendererInterface;
use Flex\Core\View\ViewResponse;
use Flex\Features\AdminUI\Configuration\AdminUIConfig;
use Flex\Features\AdminUI\Navigation\DefaultAdminNavigation;
use Flex\Features\AdminUI\Navigation\SidebarRegistry;
use Flex\Features\AdminUI\Services\AdminUIAssets;
use Flex\Features\AdminUI\Services\AdminUIRenderer;
use PHPUnit\Framework\TestCase;

final class AdminUIRendererTest extends TestCase
{
    public function testItRendersViewWithAdminUILayout(): void
    {
        $views = $this->createMock(
            ViewRendererInterface::class
        );

        $config = $this->config();
        $assets = $this->assets($config);
        $sidebars = $this->sidebars();

        $renderer = new AdminUIRenderer(
            $views,
            $assets,
            $config,
            $sidebars
        );

        $views
            ->expects(self::once())
            ->method('render')
            ->with(
                'Settings::show',
                self::callback(
                    static function (
                        array $data
                    ) use ($assets): bool {
                        return $data['title']
                            === 'Настройки'
                            && $data['adminUIAssets']
                            === $assets
                            && $data['adminUIConfig']
                            === [
                                'name' =>
                                    'Flex Admin',

                                'defaultTheme' =>
                                    'dark',

                                'turboEnabled' =>
                                    true,

                                'turboPaths' => [
                                    '/admin/settings',
                                ],
                            ]
                            && $data['adminUISidebar']['id']
                            === SidebarRegistry::DEFAULT_SIDEBAR
                            && count(
                                $data['adminUISidebars']
                            ) === 1;
                    }
                ),
                AdminUIRenderer::LAYOUT
            )
            ->willReturn(
                '<html>Settings</html>'
            );

        $html = $renderer->render(
            'Settings::show',
            [
                'title' => 'Настройки',
            ]
        );

        self::assertSame(
            '<html>Settings</html>',
            $html
        );
    }

    public function testItReturnsViewResponseWithAdminUILayout(): void
    {
        $views = $this->createMock(
            ViewRendererInterface::class
        );

        $config = $this->config();
        $assets = $this->assets($config);
        $sidebars = $this->sidebars();

        $renderer = new AdminUIRenderer(
            $views,
            $assets,
            $config,
            $sidebars
        );

        $expectedResponse = new ViewResponse(
            '<html>Settings</html>',
            201
        );

        $views
            ->expects(self::once())
            ->method('response')
            ->with(
                'Settings::show',
                self::callback(
                    static function (
                        array $data
                    ) use ($assets): bool {
                        return $data['title']
                            === 'Настройки'
                            && $data['adminUIAssets']
                            === $assets
                            && $data['adminUIConfig']['name']
                            === 'Flex Admin'
                            && $data['adminUISidebar']['id']
                            === SidebarRegistry::DEFAULT_SIDEBAR
                            && count(
                                $data['adminUISidebars']
                            ) === 1;
                    }
                ),
                AdminUIRenderer::LAYOUT,
                201
            )
            ->willReturn($expectedResponse);

        $response = $renderer->response(
            'Settings::show',
            [
                'title' => 'Настройки',
            ],
            201
        );

        self::assertSame(
            $expectedResponse,
            $response
        );
    }

    public function testReservedAdminUIDataCannotBeOverwritten(): void
    {
        $views = $this->createMock(
            ViewRendererInterface::class
        );

        $config = $this->config();
        $assets = $this->assets($config);
        $sidebars = $this->sidebars();

        $renderer = new AdminUIRenderer(
            $views,
            $assets,
            $config,
            $sidebars
        );

        $views
            ->expects(self::once())
            ->method('render')
            ->with(
                'Settings::show',
                self::callback(
                    static function (
                        array $data
                    ) use ($assets): bool {
                        return $data['adminUIAssets']
                            === $assets
                            && $data['adminUIConfig']['name']
                            === 'Flex Admin'
                            && $data['adminUISidebar']['id']
                            === SidebarRegistry::DEFAULT_SIDEBAR
                            && $data['adminUISidebars'][0]['id']
                            === SidebarRegistry::DEFAULT_SIDEBAR;
                    }
                ),
                AdminUIRenderer::LAYOUT
            )
            ->willReturn('rendered');

        $renderer->render(
            'Settings::show',
            [
                'adminUIAssets' =>
                    'invalid override',

                'adminUIConfig' => [
                    'name' => 'Invalid',
                ],

                'adminUISidebar' => [
                    'id' => 'invalid-sidebar',
                ],

                'adminUISidebars' => [
                    [
                        'id' => 'invalid-sidebar',
                    ],
                ],
            ]
        );
    }

    private function sidebars(): SidebarRegistry
    {
        $registry = new SidebarRegistry();

        (new DefaultAdminNavigation($registry))
            ->register();

        return $registry;
    }

    private function assets(AdminUIConfig $config): AdminUIAssets
    {
        return new AdminUIAssets(
            $config,
            new AdminAssetRegistry(),
            new ViteAssetResolver(
                manifestPath: __DIR__ . '/missing-manifest.json',
                development: true
            )
        );
    }

    private function config(): AdminUIConfig
    {
        $values = [
            'admin_ui.name' =>
                'Flex Admin',

            'admin_ui.default_theme' =>
                'dark',

            'admin_ui.turbo_enabled' =>
                true,

            'admin_ui.turbo_paths' => [
                '/admin/settings',
            ],
        ];

        return new AdminUIConfig(
            static fn (
                string $path,
                mixed $default = null
            ): mixed =>
                $values[$path] ?? $default
        );
    }
}
