<?php

declare(strict_types=1);

use Flex\Core\Features\Migrations\FeatureMigration;
use Illuminate\Database\Schema\Blueprint;

final class CreateShoppingProductVariantsTable extends FeatureMigration
{
    public function up(): void
    {
        $this->schema()->create(
            'shopping_product_variants',
            static function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('product_id');

                $table->string('sku', 100)->nullable()->unique();

                $table->decimal('price', 12, 2)->nullable();
                $table->decimal('compare_price', 12, 2)->nullable();
                $table->decimal('cost_price', 12, 2)->nullable();

                $table->boolean('manage_stock')->default(true);
                $table->integer('stock_quantity')->default(0);
                $table->string('stock_status', 30)->default('in_stock');

                $table->decimal('weight', 10, 3)->nullable();

                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);

                $table->timestamps();
                $table->softDeletes();

                $table->index([
                    'product_id',
                    'is_active',
                ]);

                $table->foreign('product_id')
                    ->references('id')
                    ->on('shopping_products')
                    ->cascadeOnDelete();
            }
        );
    }

    public function down(): void
    {
        $this->schema()->dropIfExists(
            'shopping_product_variants'
        );
    }
}
