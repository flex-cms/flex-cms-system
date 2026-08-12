<?php

declare(strict_types=1);

use Flex\Core\Features\Migrations\FeatureMigration;
use Illuminate\Database\Schema\Blueprint;

final class CreateShoppingProductCategoriesTable extends FeatureMigration
{
    public function up(): void
    {
        $this->schema()->create(
            'shopping_product_categories',
            static function (Blueprint $table): void {
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('category_id');

                $table->primary([
                    'product_id',
                    'category_id',
                ]);

                $table->foreign('product_id')
                    ->references('id')
                    ->on('shopping_products')
                    ->cascadeOnDelete();

                $table->foreign('category_id')
                    ->references('id')
                    ->on('shopping_categories')
                    ->cascadeOnDelete();
            }
        );
    }

    public function down(): void
    {
        $this->schema()->dropIfExists(
            'shopping_product_categories'
        );
    }
}
