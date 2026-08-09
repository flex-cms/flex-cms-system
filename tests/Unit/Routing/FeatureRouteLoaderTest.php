<?php

declare(strict_types=1);

namespace Tests\Unit\Routing;

use Flex\Core\Routing\FeatureRouteLoader;
use Flex\Core\Routing\FlexRouter;
use Flex\Core\Routing\RouteCollection;
use Flex\Core\Routing\RouteRegistrar;
use PHPUnit\Framework\TestCase;
use Flex\Core\Container\Container;
use Flex\Core\Features\Contracts\FeatureServiceProviderInterface;
use Flex\Core\Routing\Exceptions\FeatureRouteException;

final class FeatureRouteLoaderTest extends TestCase
{
    private string $path;
    private RouteCollection $routes;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'flex-features-' . bin2hex(random_bytes(6));
        mkdir($this->path, 0777, true);
        $this->routes = new RouteCollection();
        FlexRouter::setRegistrar(new RouteRegistrar($this->routes));
        FeatureRouteLoaderTestProvider::$registrations = 0;
    }

    protected function tearDown(): void
    {
        FlexRouter::reset();
        $this->deleteDirectory($this->path);
    }

    public function testItLoadsFeatureRoutesInPriorityOrder(): void
    {
        $this->feature('Users', '/users', 20);
        $this->feature('Dashboard', '/dashboard', 10);

        $loader = new FeatureRouteLoader(FlexRouter::registrar(), $this->path);
        $result = $loader->load(['web']);

        self::assertSame(['Dashboard', 'Users'], $result->loadedFeatures);
        self::assertSame(['/dashboard', '/users'], array_map(
            static fn($route): string => $route->uri(),
            $this->routes->all(),
        ));
    }

    public function testItSkipsDisabledFeatures(): void
    {
        $this->feature('Users', '/users');
        $this->feature('Media', '/media');

        $loader = new FeatureRouteLoader(
            FlexRouter::registrar(),
            $this->path,
            disabledFeatures: ['Media'],
        );
        $result = $loader->load(['web']);

        self::assertSame(['Users'], $result->loadedFeatures);
        self::assertSame(['Media'], $result->skippedFeatures);
        self::assertCount(1, $this->routes);
    }

    public function testItDoesNotLoadTheSameFileTwice(): void
    {
        $this->feature('Users', '/users');
        $loader = new FeatureRouteLoader(FlexRouter::registrar(), $this->path);

        self::assertSame(1, $loader->load(['web'])->loadedCount());
        self::assertSame(0, $loader->load(['web'])->loadedCount());
        self::assertCount(1, $this->routes);
    }

    public function testItRegistersFeatureProvider(): void
    {
        $this->feature(
            'Users',
            '/users',
            providers: [
                FeatureRouteLoaderTestProvider::class,
            ]
        );

        $container = new Container();

        $loader = new FeatureRouteLoader(
            registrar: FlexRouter::registrar(),
            featuresPath: $this->path,
            container: $container
        );

        $result = $loader->load(['web']);

        self::assertSame(
            1,
            FeatureRouteLoaderTestProvider::$registrations
        );

        self::assertSame(
            ['Users'],
            $result->loadedFeatures
        );

        self::assertInstanceOf(
            FeatureRouteLoaderTestService::class,
            $container->get(
                FeatureRouteLoaderTestContract::class
            )
        );
    }

    public function testItDoesNotRegisterProviderTwice(): void
    {
        $this->feature(
            'Users',
            '/users',
            providers: [
                FeatureRouteLoaderTestProvider::class,
            ]
        );

        $loader = new FeatureRouteLoader(
            registrar: FlexRouter::registrar(),
            featuresPath: $this->path,
            container: new Container()
        );

        $loader->load(['web']);
        $loader->load(['web']);

        self::assertSame(
            1,
            FeatureRouteLoaderTestProvider::$registrations
        );
    }

    public function testDisabledFeatureDoesNotRegisterProvider(): void
    {
        $this->feature(
            'Users',
            '/users',
            providers: [
                FeatureRouteLoaderTestProvider::class,
            ]
        );

        $loader = new FeatureRouteLoader(
            registrar: FlexRouter::registrar(),
            featuresPath: $this->path,
            disabledFeatures: ['Users'],
            container: new Container()
        );

        $result = $loader->load(['web']);

        self::assertSame(
            0,
            FeatureRouteLoaderTestProvider::$registrations
        );

        self::assertSame(
            ['Users'],
            $result->skippedFeatures
        );
    }

    public function testItRejectsProviderWithoutRequiredInterface(): void
    {
        $this->providerFeature(
            'InvalidFeature',
            [
                InvalidFeatureRouteLoaderTestProvider::class,
            ]
        );

        $loader = new FeatureRouteLoader(
            registrar: FlexRouter::registrar(),
            featuresPath: $this->path,
            container: new Container()
        );

        $this->expectException(
            FeatureRouteException::class
        );

        $this->expectExceptionMessage(
            sprintf(
                'Feature provider [%s] must implement [%s].',
                InvalidFeatureRouteLoaderTestProvider::class,
                FeatureServiceProviderInterface::class
            )
        );

        $loader->load(['web']);
    }

    public function testItRejectsProvidersWithoutContainer(): void
    {
        $this->providerFeature(
            'Users',
            [
                FeatureRouteLoaderTestProvider::class,
            ]
        );

        $loader = new FeatureRouteLoader(
            FlexRouter::registrar(),
            $this->path
        );

        $this->expectException(
            FeatureRouteException::class
        );

        $this->expectExceptionMessage(
            'Feature [Users] declares providers, '
            . 'but the Feature loader has no container.'
        );

        $loader->load(['web']);
    }

    public function testResetAllowsProviderToBeRegisteredAgain(): void
    {
        /*
         * Тук Feature-ът няма route файл, за да не се
         * получи duplicate route след reset().
         */
        $this->providerFeature(
            'Users',
            [
                FeatureRouteLoaderTestProvider::class,
            ]
        );

        $loader = new FeatureRouteLoader(
            registrar: FlexRouter::registrar(),
            featuresPath: $this->path,
            container: new Container()
        );

        $loader->load(['web']);
        $loader->reset();
        $loader->load(['web']);

        self::assertSame(
            2,
            FeatureRouteLoaderTestProvider::$registrations
        );
    }

    /**
     * @param list<class-string> $providers
     */
    private function feature(
        string $name,
        string $uri,
        int $priority = 100,
        array $providers = []
    ): void {
        $directory = $this->path
            . DIRECTORY_SEPARATOR
            . $name;

        mkdir(
            $directory . DIRECTORY_SEPARATOR . 'Routes',
            0777,
            true
        );

        file_put_contents(
            $directory . DIRECTORY_SEPARATOR . 'feature.php',
            '<?php return ' . var_export([
                'priority' => $priority,
                'providers' => $providers,
            ], true) . ';'
        );

        file_put_contents(
            $directory
            . DIRECTORY_SEPARATOR
            . 'Routes'
            . DIRECTORY_SEPARATOR
            . 'web.php',
            '<?php \\Flex\\Core\\Routing\\FlexRouter::get('
            . var_export($uri, true)
            . ', static fn () => null);'
        );
    }

    /**
     * @param list<class-string> $providers
     */
    private function providerFeature(
        string $name,
        array $providers
    ): void {
        $directory = $this->path
            . DIRECTORY_SEPARATOR
            . $name;

        mkdir($directory, 0777, true);

        file_put_contents(
            $directory . DIRECTORY_SEPARATOR . 'feature.php',
            '<?php return ' . var_export([
                'providers' => $providers,
            ], true) . ';'
        );
    }

    private function deleteDirectory(string $path): void
    {
        if (!is_dir($path)) {
            return;
        }
        foreach (array_diff(scandir($path) ?: [], ['.', '..']) as $item) {
            $target = $path . DIRECTORY_SEPARATOR . $item;
            is_dir($target) ? $this->deleteDirectory($target) : unlink($target);
        }
        rmdir($path);
    }
}

interface FeatureRouteLoaderTestContract
{
}

final class FeatureRouteLoaderTestService implements
    FeatureRouteLoaderTestContract
{
}

final class FeatureRouteLoaderTestProvider implements
    FeatureServiceProviderInterface
{
    public static int $registrations = 0;

    public function register(Container $container): void
    {
        self::$registrations++;

        $container->singleton(
            FeatureRouteLoaderTestContract::class,
            FeatureRouteLoaderTestService::class
        );
    }
}

final class InvalidFeatureRouteLoaderTestProvider
{
}
