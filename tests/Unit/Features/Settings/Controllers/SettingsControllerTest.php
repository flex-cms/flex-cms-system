<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Settings\Controllers;

use Flex\Core\Http\RedirectResponse;
use Flex\Core\Http\Request;
use Flex\Core\View\Contracts\ViewRendererInterface;
use Flex\Core\View\ViewResponse;
use Flex\Features\Settings\Configuration\SettingsPageConfig;
use Flex\Features\Settings\Controllers\SettingsController;
use Flex\Features\Settings\Exceptions\UnknownSettingsGroupException;
use Flex\Features\Settings\Repositories\SettingRepositoryInterface;
use Flex\Features\Settings\Services\SettingsService;
use InvalidArgumentException;
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

        $_SESSION = [];

        $this->repository = $this->createMock(
            SettingRepositoryInterface::class
        );

        $this->views = $this->createMock(
            ViewRendererInterface::class
        );

        $groups = [
            'general' => [
                'label' => 'Общи настройки',
                'url' => '/admin/settings/general',
            ],
            'mail' => [
                'label' => 'Имейл сървър',
                'url' => '/admin/settings/mail',
            ],
            'media' => [
                'label' => 'Файлове',
                'url' => '/admin/settings/media',
            ],
        ];

        $configuration = new SettingsPageConfig(
            static fn (
                string $path,
                mixed $default = null
            ): mixed => match ($path) {
                'settings_options.settings_page_groups' =>
                    $groups,

                'languages' => [
                    'bg' => 'Български',
                    'en' => 'English',
                ],

                'date_formats' => [
                    'd.m.Y' => 'Ден.Месец.Година',
                ],

                default => $default,
            }
        );

        $service = new SettingsService(
            $this->repository,
            $configuration
        );

        $this->controller = new SettingsController(
            $service,
            $this->views
        );
    }

    protected function tearDown(): void
    {
        unset($_SESSION['flash_success']);

        parent::tearDown();
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

        $expectedResponse = new ViewResponse(
            '<h1>Settings</h1>'
        );

        $this->views
            ->expects(self::once())
            ->method('response')
            ->with(
                'Settings::show',
                self::callback(
                    static function (array $data): bool {
                        return $data['title']
                                === 'Настройки: Общи настройки'
                            && $data['currentGroup']
                                === 'general'
                            && $data['group']
                                === 'general'
                            && $data['settings']['site_name']
                                === 'My Flex website'
                            && $data['settings']['debug_mode']
                                === true
                            && isset(
                                $data['definedGroups']['mail']
                            )
                            && isset(
                                $data['languages']['bg']
                            )
                            && isset(
                                $data['timezones']['Europe/Sofia']
                            )
                            && isset(
                                $data['dateFormats']['d.m.Y']
                            );
                    }
                ),
                'admin'
            )
            ->willReturn($expectedResponse);

        $response = $this->controller->show(
            'general'
        );

        self::assertSame(
            $expectedResponse,
            $response
        );
    }

    public function testUpdateSavesSettingsAndRedirects(): void
    {
        $this->repository
            ->expects(self::once())
            ->method('transaction')
            ->willReturnCallback(
                static fn (callable $callback): mixed =>
                    $callback()
            );

        $this->repository
            ->expects(self::once())
            ->method('saveMany')
            ->with(
                [
                    'site_name' => 'Updated website',
                    'debug_mode' => true,
                    'enable_multilang' => false,
                ],
                SettingsService::DATABASE_GROUP
            );

        $request = new Request(
            method: 'POST',
            uri: '/admin/settings/general/update',
            body: [
                'settings' => [
                    'site_name' => 'Updated website',
                    'debug_mode' => '1',
                ],
            ]
        );

        $response = $this->controller->update(
            $request,
            'general'
        );

        self::assertInstanceOf(
            RedirectResponse::class,
            $response
        );

        self::assertSame(
            '/admin/settings/general',
            $response->targetUrl()
        );

        self::assertSame(
            'Настройките бяха записани успешно.',
            $_SESSION['flash_success'] ?? null
        );
    }

    public function testUpdateRejectsInvalidSettingsPayload(): void
    {
        $this->repository
            ->expects(self::never())
            ->method('transaction');

        $this->repository
            ->expects(self::never())
            ->method('saveMany');

        $request = new Request(
            method: 'POST',
            uri: '/admin/settings/general/update',
            body: [
                'settings' => 'invalid',
            ]
        );

        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'The settings payload must be an array.'
        );

        $this->controller->update(
            $request,
            'general'
        );
    }

    public function testShowRejectsUnknownGroup(): void
    {
        $this->repository
            ->expects(self::never())
            ->method('valuesForGroup');

        $this->views
            ->expects(self::never())
            ->method('response');

        $this->expectException(
            UnknownSettingsGroupException::class
        );

        $this->controller->show('unknown');
    }
}
