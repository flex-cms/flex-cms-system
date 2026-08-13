<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Settings\Controllers;

use Flex\Core\Assets\AdminAssetRegistry;
use Flex\Core\Assets\ViteAssetResolver;
use Flex\Core\Http\JsonResponse;
use Flex\Core\Http\Request;
use Flex\Core\View\Contracts\ViewRendererInterface;
use Flex\Core\View\ViewResponse;
use Flex\Features\AdminUI\Configuration\AdminUIConfig;
use Flex\Features\AdminUI\Navigation\SidebarRegistry;
use Flex\Features\AdminUI\Services\AdminUIAssets;
use Flex\Features\AdminUI\Services\AdminUIRenderer;
use Flex\Features\Settings\Controllers\SettingsController;
use Flex\Features\Settings\Exceptions\UnknownSettingsGroupException;
use Flex\Features\Settings\Repositories\SettingRepositoryInterface;
use Flex\Features\Settings\Services\SettingsService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SettingsControllerTest extends TestCase
{
    private SettingRepositoryInterface&MockObject $repository;
    private ViewRendererInterface&MockObject $views;
    private SettingsController $controller;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(
            SettingRepositoryInterface::class
        );
        $this->views = $this->createMock(
            ViewRendererInterface::class
        );

        $config = new AdminUIConfig(
            static fn (string $path, mixed $default = null): mixed => $default
        );
        $sidebars = new SidebarRegistry();
        $sidebars->create(
            SidebarRegistry::DEFAULT_SIDEBAR,
            'Administration'
        );
        $assets = new AdminUIAssets(
            $config,
            new AdminAssetRegistry(),
            new ViteAssetResolver(
                manifestPath: __DIR__ . '/missing-manifest.json',
                development: true
            )
        );

        $this->controller = new SettingsController(
            new SettingsService($this->repository),
            new AdminUIRenderer(
                $this->views,
                $assets,
                $config,
                $sidebars
            )
        );
    }

    public function testShowReturnsSettingsViewResponse(): void
    {
        $this->repository
            ->expects(self::once())
            ->method('valuesForGroup')
            ->with(SettingsService::DATABASE_GROUP)
            ->willReturn([
                'site_name' => 'My Flex website',
                'debug_mode' => true,
            ]);

        $expectedResponse = new ViewResponse('<h1>Settings</h1>');

        $this->views
            ->expects(self::once())
            ->method('response')
            ->with(
                'Settings::groups/general',
                self::callback(
                    static fn (array $data): bool =>
                        $data['group'] === 'general'
                        && $data['storageGroup'] === 'general'
                        && $data['values']['site_name'] === 'My Flex website'
                        && $data['values']['debug_mode'] === true
                        && isset($data['languages']['bg'])
                        && isset($data['timezones']['Europe/Sofia'])
                        && isset($data['dateFormats']['d.m.Y'])
                        && isset($data['adminUIConfig'])
                        && isset($data['adminUISidebar'])
                ),
                AdminUIRenderer::LAYOUT,
                200
            )
            ->willReturn($expectedResponse);

        self::assertSame(
            $expectedResponse,
            $this->controller->show('general')
        );
    }

    public function testUpdateSavesSettingsAndReturnsJson(): void
    {
        $this->repository
            ->expects(self::once())
            ->method('transaction')
            ->willReturnCallback(
                static fn (callable $callback): mixed => $callback()
            );
        $this->repository
            ->expects(self::once())
            ->method('saveMany')
            ->with([
                'site_name' => 'Updated website',
                'debug_mode' => true,
                'enable_multilang' => false,
            ], SettingsService::DATABASE_GROUP);

        $response = $this->controller->update(
            new Request(
                method: 'POST',
                uri: '/admin/settings/general/update',
                body: ['settings' => [
                    'site_name' => 'Updated website',
                    'debug_mode' => '1',
                ]]
            ),
            'general'
        );

        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertSame(200, $response->status());
        self::assertSame('general', $this->json($response)['group']);
        self::assertTrue($this->json($response)['success']);
    }

    public function testUpdateRejectsInvalidSettingsPayload(): void
    {
        $this->repository
            ->expects(self::never())
            ->method('transaction');

        $response = $this->controller->update(
            new Request(
                method: 'POST',
                uri: '/admin/settings/general/update',
                body: ['settings' => 'invalid']
            ),
            'general'
        );

        self::assertSame(422, $response->status());
        self::assertFalse($this->json($response)['success']);
    }

    public function testShowRejectsUnknownGroup(): void
    {
        $this->repository
            ->expects(self::never())
            ->method('valuesForGroup');
        $this->views
            ->expects(self::never())
            ->method('response');

        $this->expectException(UnknownSettingsGroupException::class);

        $this->controller->show('unknown');
    }

    /** @return array<string, mixed> */
    private function json(JsonResponse $response): array
    {
        return json_decode(
            $response->content(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }
}
