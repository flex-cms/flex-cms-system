<?php

namespace Flex\Core\Plugins\Migrations\Contracts;

use Illuminate\Database\Connection;

interface PluginMigrationInterface
{
    public function configure(Connection $connection, string $tablePrefix): void;

    public function up(): void;

    public function down(): void;

    public function shouldUseTransaction(): bool;
}
