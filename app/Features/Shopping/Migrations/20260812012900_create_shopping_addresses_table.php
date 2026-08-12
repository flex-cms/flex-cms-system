<?php

declare(strict_types=1);

use Flex\Core\Features\Migrations\FeatureMigration;
use Illuminate\Database\Schema\Blueprint;

final class CreateShoppingAddressesTable extends FeatureMigration
{
    public function up(): void
    {
        $this->schema()->create(
            'shopping_addresses',
            static function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('customer_id');

                $table->string('type', 30)->default('shipping');

                $table->string('first_name', 120);
                $table->string('last_name', 120);
                $table->string('company', 190)->nullable();

                $table->string('country_code', 2)->default('BG');
                $table->string('city', 120);
                $table->string('postal_code', 30)->nullable();
                $table->string('address_line_1', 255);
                $table->string('address_line_2', 255)->nullable();

                $table->string('phone', 50)->nullable();
                $table->boolean('is_default')->default(false);

                $table->timestamps();

                $table->index([
                    'customer_id',
                    'type',
                ]);

                $table->foreign('customer_id')
                    ->references('id')
                    ->on('shopping_customers')
                    ->cascadeOnDelete();
            }
        );
    }

    public function down(): void
    {
        $this->schema()->dropIfExists(
            'shopping_addresses'
        );
    }
}
