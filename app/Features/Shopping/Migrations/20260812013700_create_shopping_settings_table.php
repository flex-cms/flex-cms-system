<?php

declare(strict_types=1);

use Flex\Core\Features\Migrations\FeatureMigration;
use Illuminate\Database\Schema\Blueprint;

final class CreateShoppingSettingsTable extends FeatureMigration
{
    public function up(): void
    {
        $this->schema()->create(
            'shopping_settings',
            static function (Blueprint $table): void {
                $table->bigIncrements('id');

                $table->string('group', 100)->default('general');
                $table->string('key', 190);
                $table->longText('value')->nullable();
                $table->string('type', 30)->default('string');

                $table->timestamps();

                $table->unique([
                    'group',
                    'key',
                ]);
            }
        );
    }

    public function down(): void
    {
        $this->schema()->dropIfExists(
            'shopping_settings'
        );
    }
}
