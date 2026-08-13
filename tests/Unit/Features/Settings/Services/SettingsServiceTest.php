<?php

declare(strict_types=1);

namespace Tests\Unit\Features\Settings\Services;

use Flex\Features\Settings\Exceptions\UnknownSettingsGroupException;
use Flex\Features\Settings\Repositories\SettingRepositoryInterface;
use Flex\Features\Settings\Services\SettingsService;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class SettingsServiceTest extends TestCase
{
    private SettingRepositoryInterface&MockObject $repository;

    private SettingsService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(
            SettingRepositoryInterface::class
        );

        $this->service = new SettingsService($this->repository);
    }

    public function testItReturnsStoredValuesMergedWithDefaults(): void
    {
        $this->repository
            ->expects(self::once())
            ->method('valuesForGroup')
            ->with(SettingsService::DATABASE_GROUP)
            ->willReturn([
                'site_name' => 'My website',
                'debug_mode' => true,
                'unrelated_setting' => 'ignored',
            ]);

        $values = $this->service->valuesForPage('general');

        self::assertSame('My website', $values['site_name']);
        self::assertTrue($values['debug_mode']);

        self::assertSame('', $values['admin_email']);
        self::assertSame('Europe/Sofia', $values['timezone']);
        self::assertSame('d.m.Y', $values['date_format']);
        self::assertFalse($values['enable_multilang']);

        self::assertArrayNotHasKey(
            'unrelated_setting',
            $values
        );
    }

    public function testItReturnsMailDefaults(): void
    {
        $this->repository
            ->method('valuesForGroup')
            ->willReturn([]);

        $values = $this->service->valuesForPage('mail');

        self::assertSame('', $values['smtp_host']);
        self::assertSame(587, $values['smtp_port']);
        self::assertSame('tls', $values['smtp_encryption']);
        self::assertSame('', $values['smtp_pass']);
    }

    public function testItReturnsMediaDefaults(): void
    {
        $this->repository
            ->method('valuesForGroup')
            ->willReturn([]);

        $values = $this->service->valuesForPage('media');

        self::assertTrue($values['media_use_date_folders']);
        self::assertFalse($values['media_keep_original_name']);
        self::assertSame(5, $values['media_max_size']);
        self::assertSame(
            'jpg,png,webp',
            $values['media_allowed_extensions']
        );
    }

    public function testItRejectsUnknownPageGroup(): void
    {
        $this->repository
            ->expects(self::never())
            ->method('valuesForGroup');

        $this->expectException(UnknownSettingsGroupException::class);

        $this->expectExceptionMessage(
            'Unknown settings page group [unknown].'
        );

        $this->service->valuesForPage('unknown');
    }

    public function testItNormalizesAndSavesGeneralSettings(): void
    {
        $this->repository
            ->expects(self::once())
            ->method('transaction')
            ->willReturnCallback(
                static fn(callable $callback): mixed => $callback()
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

        $this->service->updatePage('general', [
            'site_name' => 'Updated website',
            'debug_mode' => '1',
            'unknown_setting' => 'must not be saved',
        ]);
    }

    public function testMissingBooleanFieldsAreSavedAsFalse(): void
    {
        $this->repository
            ->method('transaction')
            ->willReturnCallback(
                static fn(callable $callback): mixed => $callback()
            );

        $this->repository
            ->expects(self::once())
            ->method('saveMany')
            ->with(
                [
                    'debug_mode' => false,
                    'enable_multilang' => false,
                ],
                SettingsService::DATABASE_GROUP
            );

        $this->service->updatePage('general', []);
    }

    public function testItConvertsIntegerSettings(): void
    {
        $this->repository
            ->method('transaction')
            ->willReturnCallback(
                static fn(callable $callback): mixed => $callback()
            );

        $this->repository
            ->expects(self::once())
            ->method('saveMany')
            ->with(
                [
                    'smtp_port' => 465,
                ],
                SettingsService::DATABASE_GROUP
            );

        $this->service->updatePage('mail', [
            'smtp_port' => '465',
        ]);
    }

    public function testEmptySmtpPasswordIsNotOverwritten(): void
    {
        $this->repository
            ->method('transaction')
            ->willReturnCallback(
                static fn(callable $callback): mixed => $callback()
            );

        $this->repository
            ->expects(self::once())
            ->method('saveMany')
            ->with(
                [
                    'smtp_host' => 'smtp.example.com',
                ],
                SettingsService::DATABASE_GROUP
            );

        $this->service->updatePage('mail', [
            'smtp_host' => 'smtp.example.com',
            'smtp_pass' => '',
        ]);
    }

    public function testNonEmptySmtpPasswordIsSaved(): void
    {
        $this->repository
            ->method('transaction')
            ->willReturnCallback(
                static fn(callable $callback): mixed => $callback()
            );

        $this->repository
            ->expects(self::once())
            ->method('saveMany')
            ->with(
                [
                    'smtp_pass' => 'new-secret-password',
                ],
                SettingsService::DATABASE_GROUP
            );

        $this->service->updatePage('mail', [
            'smtp_pass' => 'new-secret-password',
        ]);
    }

    public function testItRejectsInvalidIntegerValue(): void
    {
        $this->repository
            ->expects(self::never())
            ->method('transaction');

        $this->repository
            ->expects(self::never())
            ->method('saveMany');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The setting [media_max_size] must contain an integer.'
        );

        $this->service->updatePage('media', [
            'media_max_size' => 'not-a-number',
        ]);
    }

    public function testItRejectsArrayForScalarSetting(): void
    {
        $this->repository
            ->expects(self::never())
            ->method('transaction');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The setting [site_name] must contain a scalar value.'
        );

        $this->service->updatePage('general', [
            'site_name' => ['invalid'],
        ]);
    }

    public function testItCreatesSettingsPageData(): void
    {
        $this->repository
            ->expects(self::once())
            ->method('valuesForGroup')
            ->with(SettingsService::DATABASE_GROUP)
            ->willReturn([
                'site_name' => 'My Flex website',
                'debug_mode' => true,
            ]);

        $pageData = $this->service->pageData('general');

        self::assertSame(
            'Настройки: Общи настройки',
            $pageData->title
        );

        self::assertSame(
            'general',
            $pageData->group
        );

        self::assertSame(
            'My Flex website',
            $pageData->values['site_name']
        );

        self::assertTrue(
            $pageData->values['debug_mode']
        );

        self::assertSame(
            SettingsService::DATABASE_GROUP,
            $pageData->storageGroup
        );

        self::assertArrayHasKey(
            'Europe/Sofia',
            $pageData->timezones
        );
    }

    public function testPageDataRejectsUnknownGroup(): void
    {
        $this->repository
            ->expects(self::never())
            ->method('valuesForGroup');

        $this->expectException(
            UnknownSettingsGroupException::class
        );

        $this->service->pageData('unknown');
    }
}
