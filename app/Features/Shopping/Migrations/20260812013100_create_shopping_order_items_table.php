<?php

declare(strict_types=1);

use Flex\Core\Features\Migrations\FeatureMigration;
use Illuminate\Database\Schema\Blueprint;

final class CreateShoppingOrderItemsTable extends FeatureMigration
{
    public function up(): void
    {
        $this->schema()->create(
            'shopping_order_items',
            static function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('order_id');

                $table->unsignedBigInteger('product_id')->nullable();
                $table->unsignedBigInteger('variant_id')->nullable();

                // Product snapshot at order time.
                $table->string('product_name', 255);
                $table->string('sku', 100)->nullable();
                $table->json('variant_data')->nullable();

                $table->decimal('unit_price', 12, 2)->default(0);
                $table->unsignedInteger('quantity')->default(1);
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->decimal('discount_total', 12, 2)->default(0);
                $table->decimal('tax_total', 12, 2)->default(0);
                $table->decimal('total', 12, 2)->default(0);

                $table->timestamps();

                $table->index('order_id');
                $table->index('product_id');
                $table->index('variant_id');

                $table->foreign('order_id')
                    ->references('id')
                    ->on('shopping_orders')
                    ->cascadeOnDelete();

                $table->foreign('product_id')
                    ->references('id')
                    ->on('shopping_products')
                    ->nullOnDelete();

                $table->foreign('variant_id')
                    ->references('id')
                    ->on('shopping_product_variants')
                    ->nullOnDelete();
            }
        );
    }

    public function down(): void
    {
        $this->schema()->dropIfExists(
            'shopping_order_items'
        );
    }
}
