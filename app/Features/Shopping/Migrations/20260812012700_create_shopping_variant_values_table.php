<?php

declare(strict_types=1);

use Flex\Core\Features\Migrations\FeatureMigration;
use Illuminate\Database\Schema\Blueprint;

final class CreateShoppingVariantValuesTable extends FeatureMigration
{
    public function up(): void
    {
        $this->schema()->create(
            'shopping_variant_values',
            static function (Blueprint $table): void {
                $table->unsignedBigInteger('variant_id');
                $table->unsignedBigInteger('attribute_value_id');

                $table->primary([
                    'variant_id',
                    'attribute_value_id',
                ]);

                $table->foreign('variant_id')
                    ->references('id')
                    ->on('shopping_product_variants')
                    ->cascadeOnDelete();

                $table->foreign('attribute_value_id')
                    ->references('id')
                    ->on('shopping_attribute_values')
                    ->cascadeOnDelete();
            }
        );
    }

    public function down(): void
    {
        $this->schema()->dropIfExists(
            'shopping_variant_values'
        );
    }
}
