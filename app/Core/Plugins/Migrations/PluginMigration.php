<?php

namespace Flex\Core\Plugins\Migrations;

use Flex\Core\Plugins\Migrations\Contracts\PluginMigrationInterface;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder;
use LogicException;
use InvalidArgumentException;

abstract class PluginMigration implements PluginMigrationInterface
{
    private ?Connection $connection = null;
    private string $tablePrefix = '';

    final public function configure(Connection $connection, string $tablePrefix): void
    {
        if (!preg_match('/^[a-z][a-z0-9_]*$/', $tablePrefix)) {
            throw new InvalidArgumentException(
                'Префиксът на таблиците трябва да съдържа само малки латински букви, цифри и долна черта.'
            );
        }

        $this->connection = $connection;
        $this->tablePrefix = rtrim($tablePrefix, '_') . '_';
    }

    final protected function connection(): Connection
    {
        if (!$this->connection) {
            throw new LogicException('Migration контекстът не е конфигуриран.');
        }

        return $this->connection;
    }

    final protected function schema(): Builder
    {
        return $this->connection()->getSchemaBuilder();
    }

    final protected function table(string $name): string
    {
        if (!preg_match('/^[a-z][a-z0-9_]*$/', $name)) {
            throw new InvalidArgumentException('Невалидно име на plugin таблица.');
        }

        return $this->tablePrefix . $name;
    }

    public function shouldUseTransaction(): bool
    {
        return false;
    }
}
