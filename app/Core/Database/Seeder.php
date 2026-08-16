<?php

declare(strict_types=1);

namespace Flex\Core\Database;

use InvalidArgumentException;

abstract class Seeder
{
    abstract public function run(): void;

    protected function call(string|array $seeders): void
    {
        foreach ((array) $seeders as $seederClass) {
            if (!class_exists($seederClass)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Seeder class [%s] does not exist.',
                        $seederClass
                    )
                );
            }

            $seeder = new $seederClass();

            if (!$seeder instanceof self) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Seeder [%s] must extend [%s].',
                        $seederClass,
                        self::class
                    )
                );
            }

            $seeder->run();
        }
    }
}
