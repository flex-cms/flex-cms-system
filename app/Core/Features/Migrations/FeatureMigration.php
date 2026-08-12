<?php

declare(strict_types=1);

namespace Flex\Core\Features\Migrations;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder;
use RuntimeException;

abstract class FeatureMigration
{
    final protected function connection(): Connection
    {
        return Capsule::connection();
    }

    final protected function schema(): Builder
    {
        return $this->connection()
            ->getSchemaBuilder();
    }

    /**
     * Изпълнява migration-а.
     *
     * Новите migration класове трябва да override-нат up().
     * За обратна съвместимост стар migration с change()
     * също може да бъде изпълнен.
     */
    public function up(): void
    {
        if (
            method_exists($this, 'change')
            && get_class($this) !== self::class
        ) {
            $this->change();

            return;
        }

        throw new RuntimeException(
            sprintf(
                'Migration класът "%s" трябва да съдържа up().',
                static::class
            )
        );
    }

    /**
     * Връща migration-а.
     *
     * Новите migration класове трябва да override-нат down().
     */
    public function down(): void
    {
        throw new RuntimeException(
            sprintf(
                'Migration класът "%s" не поддържа rollback. Добавете down().',
                static::class
            )
        );
    }
}
