<?php

declare(strict_types=1);

namespace Tests\Unit\Routing;

use Flex\Core\Routing\FeatureRouteLoader;
use Flex\Core\Routing\FlexRouter;
use Flex\Core\Routing\RouteCollection;
use Flex\Core\Routing\RouteRegistrar;
use PHPUnit\Framework\TestCase;

final class FeatureRouteLoaderTest extends TestCase
{
    private string $path;
    private RouteCollection $routes;

    protected function setUp(): void
    {
        $this->path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'flex-features-' . bin2hex(random_bytes(6));
        mkdir($this->path, 0777, true);
        $this->routes = new RouteCollection();
        FlexRouter::setRegistrar(new RouteRegistrar($this->routes));
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
            static fn ($route): string => $route->uri(),
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

    private function feature(string $name, string $uri, int $priority = 100): void
    {
        $directory = $this->path . DIRECTORY_SEPARATOR . $name;
        mkdir($directory . DIRECTORY_SEPARATOR . 'Routes', 0777, true);
        file_put_contents(
            $directory . DIRECTORY_SEPARATOR . 'feature.php',
            '<?php return ' . var_export(['priority' => $priority], true) . ';',
        );
        file_put_contents(
            $directory . DIRECTORY_SEPARATOR . 'Routes' . DIRECTORY_SEPARATOR . 'web.php',
            '<?php \\Flex\\Core\\Routing\\FlexRouter::get(' . var_export($uri, true) . ', static fn () => null);',
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
