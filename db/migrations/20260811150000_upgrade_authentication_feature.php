<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UpgradeAuthenticationFeature extends AbstractMigration
{
    public function up(): void
    {
        $users = $this->table('users');
        if (!$users->hasColumn('is_super_admin')) {
            $users->addColumn('is_super_admin', 'boolean', ['default' => false, 'null' => false, 'after' => 'is_active'])->update();
        }

        // NULL values do not collide in a UNIQUE index; only a single generated value 1 is allowed.
        if (!$users->hasColumn('super_admin_slot')) {
            $this->execute("ALTER TABLE `users` ADD COLUMN `super_admin_slot` TINYINT GENERATED ALWAYS AS (CASE WHEN `is_super_admin` = 1 THEN 1 ELSE NULL END) STORED");
        }
        $indexes = $this->fetchAll("SHOW INDEX FROM `users` WHERE `Key_name` = 'users_single_super_admin_unique'");
        if ($indexes === []) {
            $this->execute('ALTER TABLE `users` ADD UNIQUE INDEX `users_single_super_admin_unique` (`super_admin_slot`)');
        }
    }

    public function down(): void
    {
        $this->execute('ALTER TABLE `users` DROP INDEX `users_single_super_admin_unique`');
        $this->execute('ALTER TABLE `users` DROP COLUMN `super_admin_slot`');
        $this->table('users')->removeColumn('is_super_admin')->update();
    }
}
