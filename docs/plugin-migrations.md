# Plugin migrations

Flex CMS tracks plugin schema changes independently from the plugin version in
the `plugin_migrations` table. Migration files are executed once, in filename
order, and their SHA-256 checksum is retained to detect edits after release.

## Plugin structure

```text
database/
└── migrations/
    ├── 20260729090000_create_products_table.php
    └── 20260729100000_add_sku_to_products_table.php
```

Names must follow `YYYYMMDDHHMMSS_description.php`. Published migrations must
not be edited; create a new migration for every subsequent schema change.

## Migration file

```php
<?php

use Flex\Core\Plugins\Migrations\PluginMigration;
use Illuminate\Database\Schema\Blueprint;

return new class extends PluginMigration
{
    public function up(): void
    {
        $this->schema()->create($this->table('products'), function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('price', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $this->schema()->dropIfExists($this->table('products'));
    }
};
```

The configured table prefix is applied by `table()`. A `shop` prefix therefore
produces `shop_products`. Prefixes and table names accept lowercase Latin
letters, numbers, and underscores only.

Migrations are non-transactional by default because MySQL DDL statements can
implicitly commit. A data-only migration may opt into a transaction:

```php
public function shouldUseTransaction(): bool
{
    return true;
}
```

## Running pending migrations

```php
use Flex\Core\Plugins\Migrations\PluginMigrationManager;
use Illuminate\Database\Capsule\Manager as Capsule;

$manager = new PluginMigrationManager(Capsule::connection());

$result = $manager->migrate(
    pluginSlug: 'flex-shop',
    pluginVersion: '1.1.0',
    migrationsPath: plugins_path('flex-shop/database/migrations'),
    tablePrefix: 'shop',
);
```

Calling `migrate()` again is safe: only pending files are executed. Concurrent
MySQL runs for the same plugin are protected by an advisory lock.

## Rolling back

`rollback()` executes `down()` for the latest completed batch in reverse order:

```php
$result = $manager->rollback(
    pluginSlug: 'flex-shop',
    migrationsPath: plugins_path('flex-shop/database/migrations'),
    tablePrefix: 'shop',
);
```

Rollback is blocked if a recorded migration file is missing or its checksum has
changed. Plugin uninstall remains a separate operation and must not implicitly
delete business data.