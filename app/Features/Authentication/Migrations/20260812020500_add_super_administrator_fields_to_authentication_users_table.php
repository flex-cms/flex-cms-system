<?php

declare(strict_types=1);

use Flex\Core\Features\Migrations\FeatureMigration;
use Flex\Features\Authentication\Support\AuthenticationTables;
use Illuminate\Database\Schema\Blueprint;

final class AddSuperAdministratorFieldsToAuthenticationUsersTable extends FeatureMigration
{
    public function up(): void
    {
        if (!$this->schema()->hasColumn(AuthenticationTables::users(), 'is_super_admin')) {
            $this->schema()->table(
                AuthenticationTables::users(),
                static function (Blueprint $table): void {
                    $table->boolean('is_super_admin')
                        ->default(false)
                        ->after('is_active');
                }
            );
        }

        // NULL values do not collide in a UNIQUE index. Therefore only one
        // generated value 1 can exist, while all regular users receive NULL.
        if (!$this->schema()->hasColumn(AuthenticationTables::users(), 'super_admin_slot')) {
            $this->connection()->statement(
                'ALTER TABLE `' . AuthenticationTables::users() . '` '
                . 'ADD COLUMN `super_admin_slot` TINYINT '
                . 'GENERATED ALWAYS AS ('
                . 'CASE WHEN `is_super_admin` = 1 THEN 1 ELSE NULL END'
                . ') STORED'
            );
        }

        if (!$this->hasSuperAdministratorIndex()) {
            $this->connection()->statement(
                'ALTER TABLE `' . AuthenticationTables::users() . '` '
                . 'ADD UNIQUE INDEX `' . AuthenticationTables::superAdministratorIndex() . '` '
                . '(`super_admin_slot`)'
            );
        }
    }

    public function down(): void
    {
        if (!$this->schema()->hasTable(AuthenticationTables::users())) {
            return;
        }

        if ($this->hasSuperAdministratorIndex()) {
            $this->connection()->statement(
                'ALTER TABLE `' . AuthenticationTables::users() . '` '
                . 'DROP INDEX `' . AuthenticationTables::superAdministratorIndex() . '`'
            );
        }

        if ($this->schema()->hasColumn(AuthenticationTables::users(), 'super_admin_slot')) {
            $this->schema()->table(
                AuthenticationTables::users(),
                static function (Blueprint $table): void {
                    $table->dropColumn('super_admin_slot');
                }
            );
        }

        if ($this->schema()->hasColumn(AuthenticationTables::users(), 'is_super_admin')) {
            $this->schema()->table(
                AuthenticationTables::users(),
                static function (Blueprint $table): void {
                    $table->dropColumn('is_super_admin');
                }
            );
        }
    }

    private function hasSuperAdministratorIndex(): bool
    {
        return $this->connection()->select(
            'SHOW INDEX FROM `' . AuthenticationTables::users() . '` '
            . "WHERE `Key_name` = '" . AuthenticationTables::superAdministratorIndex() . "'"
        ) !== [];
    }
}
