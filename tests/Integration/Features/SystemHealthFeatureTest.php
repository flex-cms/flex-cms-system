<?php

declare(strict_types=1);

namespace Tests\Integration\Features;

use Flex\Core\Http\Request;
use Flex\Core\Routing\FlexRouter;
use Flex\Core\Routing\FlexRouterApplication;
use PHPUnit\Framework\TestCase;

final class SystemHealthFeatureTest extends TestCase
{
    protected function tearDown(): void
    {
        FlexRouter::reset();
    }

    public function testHealthEndpointIsLoadedAndExecutedByFlexRouter(): void
    {
        $root = dirname(__DIR__, 3);
        $app = FlexRouterApplication::create(
            baseUrl: 'https://flex.test',
            passNotFound: true,
        );

        $result = $app
            ->featureRoutes(
                $root . '/app/Features',
                enabledFeatures: ['SystemHealth']
            )
            ->load(['api']);

        self::assertSame(
            ['SystemHealth'],
            $result->loadedFeatures
        );

        $kernelResult = $app->kernel->handle(
            new Request('GET', '/api/flex/health')
        );

        self::assertTrue($kernelResult->isHandled());
        self::assertSame(200, $kernelResult->response()->status());

        $payload = json_decode(
            $kernelResult->response()->content(),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        self::assertSame('ok', $payload['status']);
        self::assertSame('GET', $payload['request']['method']);
        self::assertSame('/api/flex/health', $payload['request']['path']);
        self::assertSame(
            'https://flex.test/api/flex/health',
            FlexRouter::route('api.flex.health'),
        );
    }
}
