<?php

declare(strict_types=1);

use Flex\Core\Features\Migrations\FeatureMigration;
use Illuminate\Database\Schema\Blueprint;

final class CreateShoppingCustomersTable extends FeatureMigration
{
    public function up(): void
    {
        $this->schema()->create(
            'shopping_customers',
            static function (Blueprint $table): void {
                $table->bigIncrements('id');

                // Optional relation to a Flex user without forcing a FK
                // to the core users table/schema.
                $table->string('user_id', 64)->nullable()->index();

                $table->string('first_name', 120);
                $table->string('last_name', 120);
                $table->string('email', 190)->nullable()->index();
                $table->string('phone', 50)->nullable();

                $table->boolean('is_active')->default(true);

                $table->timestamps();
                $table->softDeletes();
            }
        );
    }

    public function down(): void
    {
        $this->schema()->dropIfExists(
            'shopping_customers'
        );
    }
}
