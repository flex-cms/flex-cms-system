<?php

declare(strict_types=1);

namespace Flex\Core\Database;

use InvalidArgumentException;
use ReflectionClass;

final class SeederDiscovery
{
    public function __construct(
        private readonly string $featuresPath,
        private readonly string $featuresNamespace = 'Flex\\Features',
    ) {
    }

    /**
     * Намира root seeders за всички features.
     *
     * Конвенция:
     *
     * Authentication/
     * └── Seeders/
     *     └── AuthenticationSeeder.php
     *
     * Shopping/
     * └── Seeders/
     *     └── ShoppingSeeder.php
     *
     * @return array<class-string<Seeder>>
     */
    public function discover(): array
    {
        if (!is_dir($this->featuresPath)) {
            return [];
        }

        $seeders = [];

        foreach ($this->featureDirectories() as $featurePath) {
            $featureName = basename($featurePath);

            $seeder = $this->discoverFeature($featureName);

            if ($seeder !== null) {
                $seeders[] = $seeder;
            }
        }

        return $seeders;
    }

    /**
     * Намира root seeder за конкретен Feature.
     *
     * Например:
     *
     * discoverFeature('Authentication')
     *
     * ще търси:
     *
     * Flex\Features\Authentication\Seeders\AuthenticationSeeder
     *
     * @return class-string<Seeder>|null
     */
    public function discoverFeature(string $featureName): ?string
    {
        $featureName = trim($featureName);

        if ($featureName === '') {
            throw new InvalidArgumentException(
                'Feature name cannot be empty.'
            );
        }

        $seederFile = sprintf(
            '%s/%s/Seeders/%sSeeder.php',
            rtrim($this->featuresPath, '/\\'),
            $featureName,
            $featureName,
        );

        if (!is_file($seederFile)) {
            return null;
        }

        $seederClass = sprintf(
            '%s\\%s\\Seeders\\%sSeeder',
            trim($this->featuresNamespace, '\\'),
            $featureName,
            $featureName,
        );

        if (!class_exists($seederClass)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Seeder class [%s] was expected for file [%s], but the class could not be loaded.',
                    $seederClass,
                    $seederFile,
                )
            );
        }

        $reflection = new ReflectionClass($seederClass);

        if (!$reflection->isSubclassOf(Seeder::class)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Seeder [%s] must extend [%s].',
                    $seederClass,
                    Seeder::class,
                )
            );
        }

        if ($reflection->isAbstract()) {
            throw new InvalidArgumentException(
                sprintf(
                    'Seeder [%s] cannot be abstract.',
                    $seederClass,
                )
            );
        }

        /** @var class-string<Seeder> $seederClass */
        return $seederClass;
    }

    /**
     * @return array<string>
     */
    public function availableFeatures(): array
    {
        $features = [];

        foreach ($this->featureDirectories() as $featurePath) {
            $featureName = basename($featurePath);

            if ($this->discoverFeature($featureName) !== null) {
                $features[] = $featureName;
            }
        }

        return $features;
    }

    /**
     * @return array<string>
     */
    private function featureDirectories(): array
    {
        $directories = glob(
            rtrim($this->featuresPath, '/\\') . '/*',
            GLOB_ONLYDIR,
        );

        if ($directories === false) {
            return [];
        }

        sort($directories);

        return $directories;
    }
}
