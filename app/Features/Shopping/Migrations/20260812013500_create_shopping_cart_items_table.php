<?php

declare(strict_types=1);

use Flex\Core\Features\Migrations\FeatureMigration;
use Illuminate\Database\Schema\Blueprint;

final class CreateShoppingCartItemsTable extends FeatureMigration
{
    public function up(): void
    {
        $this->schema()->create(
            'shopping_cart_items',
            static function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('cart_id');
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('variant_id')->nullable();

                $table->unsignedInteger('quantity')->default(1);

                $table->timestamps();

                $table->unique([
                    'cart_id',
                    'product_id',
                    'variant_id',
                ], 'shopping_cart_items_unique');

                $table->foreign('cart_id')
                    ->references('id')
                    ->on('shopping_carts')
                    ->cascadeOnDelete();

                $table->foreign('product_id')
                    ->references('id')
                    ->on('shopping_products')
                    ->cascadeOnDelete();

                $table->foreign('variant_id')
                    ->references('id')
                    ->on('shopping_product_variants')
                    ->cascadeOnDelete();
            }
        );
    }

    public function down(): void
    {
        $this->schema()->dropIfExists(
            'shopping_cart_items'
        );
    }
}
