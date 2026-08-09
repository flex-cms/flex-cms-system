<?php

declare(strict_types=1);

namespace Flex\Core\Routing;

use FilesystemIterator;
use Flex\Core\Routing\Exceptions\FeatureRouteException;

final class FeatureRouteLoader
{
    private const DEFAULT_ROUTE_FILES = [
        'web' => 'Routes/web.php',
        'admin' => 'Routes/admin.php',
        'api' => 'Routes/api.php',
    ];

    /** @var array<string, true> */
    private array $loadedFiles = [];

    /**
     * @param list<string>|null $enabledFeatures Null means every discovered feature.
     * @param list<string> $disabledFeatures
     */
    public function __construct(
        private readonly RouteRegistrar $registrar,
        private readonly string $featuresPath,
        private readonly ?array $enabledFeatures = null,
        private readonly array $disabledFeatures = [],
    ) {
    }

    /** @param list<string> $routeTypes */
    public function load(array $routeTypes = ['web', 'admin', 'api']): FeatureRouteLoadResult
    {
        $this->assertRouteTypes($routeTypes);
        $definitions = $this->discover();
        $loadedFeatures = [];
        $skippedFeatures = [];
        $loadedFiles = [];

        foreach ($definitions as $definition) {
            $name = $definition['name'];
            if (!$this->isEnabled($name, $definition['enabled'])) {
                $skippedFeatures[] = $name;
                continue;
            }

            $featureLoaded = false;
            foreach ($routeTypes as $type) {
                $relative = $definition['routes'][$type] ?? null;
                if (!is_string($relative) || trim($relative) === '') {
                    continue;
                }

                $file = $this->resolveRouteFile($definition['path'], $relative);
                if ($file === null || isset($this->loadedFiles[$file])) {
                    continue;
                }

                $this->registrar->group([], $file);
                $this->loadedFiles[$file] = true;
                $loadedFiles[] = $file;
                $featureLoaded = true;
            }

            if ($featureLoaded) {
                $loadedFeatures[] = $name;
            }
        }

        return new FeatureRouteLoadResult(
            array_values(array_unique($loadedFeatures)),
            array_values(array_unique($skippedFeatures)),
            $loadedFiles,
        );
    }

    public function reset(): void
    {
        $this->loadedFiles = [];
    }

    /**
     * @return list<array{
     *   name: string,
     *   path: string,
     *   enabled: bool,
     *   priority: int,
     *   routes: array<string, string>
     * }>
     */
    private function discover(): array
    {
        if (!is_dir($this->featuresPath)) {
            return [];
        }

        $definitions = [];
        foreach (new FilesystemIterator($this->featuresPath, FilesystemIterator::SKIP_DOTS) as $item) {
            if (!$item->isDir()) {
                continue;
            }

            $name = $item->getFilename();
            $this->assertFeatureName($name);
            $path = $item->getRealPath();
            if ($path === false) {
                continue;
            }

            $manifest = $this->manifest($path);
            $definitions[] = [
                'name' => $name,
                'path' => $path,
                'enabled' => (bool) ($manifest['enabled'] ?? true),
                'priority' => (int) ($manifest['priority'] ?? 100),
                'routes' => array_replace(
                    self::DEFAULT_ROUTE_FILES,
                    is_array($manifest['routes'] ?? null) ? $manifest['routes'] : [],
                ),
            ];
        }

        usort($definitions, static function (array $left, array $right): int {
            return [$left['priority'], strtolower($left['name'])]
                <=> [$right['priority'], strtolower($right['name'])];
        });

        return $definitions;
    }

    /** @return array<string, mixed> */
    private function manifest(string $featurePath): array
    {
        $file = $featurePath . DIRECTORY_SEPARATOR . 'feature.php';
        if (!is_file($file)) {
            return [];
        }

        $manifest = require $file;
        if (!is_array($manifest)) {
            throw new FeatureRouteException("Feature manifest [{$file}] must return an array.");
        }

        return $manifest;
    }

    private function resolveRouteFile(string $featurePath, string $relative): ?string
    {
        if (str_contains($relative, "\0") || str_starts_with($relative, '/') || preg_match('/^[A-Za-z]:[\\\\\/]/', $relative)) {
            throw new FeatureRouteException("Route path [{$relative}] must be relative to its Feature.");
        }

        $candidate = $featurePath . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);
        if (!is_file($candidate)) {
            return null;
        }

        $realFeature = realpath($featurePath);
        $realFile = realpath($candidate);
        if ($realFeature === false || $realFile === false) {
            return null;
        }

        $prefix = rtrim($realFeature, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (!str_starts_with($realFile, $prefix)) {
            throw new FeatureRouteException("Route file [{$relative}] escapes its Feature directory.");
        }

        return $realFile;
    }

    private function isEnabled(string $name, bool $manifestEnabled): bool
    {
        if (!$manifestEnabled || in_array($name, $this->disabledFeatures, true)) {
            return false;
        }

        return $this->enabledFeatures === null || in_array($name, $this->enabledFeatures, true);
    }

    /** @param list<string> $routeTypes */
    private function assertRouteTypes(array $routeTypes): void
    {
        foreach ($routeTypes as $type) {
            if (!array_key_exists($type, self::DEFAULT_ROUTE_FILES)) {
                throw new FeatureRouteException("Unsupported Feature route type [{$type}].");
            }
        }
    }

    private function assertFeatureName(string $name): void
    {
        if (!preg_match('/^[A-Za-z][A-Za-z0-9_-]*$/', $name)) {
            throw new FeatureRouteException("Invalid Feature directory name [{$name}].");
        }
    }
}
