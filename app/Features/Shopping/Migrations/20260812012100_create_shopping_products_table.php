<?php

declare(strict_types=1);

use Flex\Core\Features\Migrations\FeatureMigration;
use Illuminate\Database\Schema\Blueprint;

final class CreateShoppingProductsTable extends FeatureMigration
{
    public function up(): void
    {
        $this->schema()->create(
            'shopping_products',
            static function (Blueprint $table): void {
                $table->bigIncrements('id');

                $table->string('name', 255);
                $table->string('slug', 190)->unique();
                $table->string('sku', 100)->nullable()->unique();

                $table->text('short_description')->nullable();
                $table->longText('description')->nullable();

                $table->decimal('price', 12, 2)->default(0);
                $table->decimal('compare_price', 12, 2)->nullable();
                $table->decimal('cost_price', 12, 2)->nullable();

                $table->boolean('manage_stock')->default(true);
                $table->integer('stock_quantity')->default(0);
                $table->string('stock_status', 30)->default('in_stock');

                $table->decimal('weight', 10, 3)->nullable();
                $table->decimal('length', 10, 2)->nullable();
                $table->decimal('width', 10, 2)->nullable();
                $table->decimal('height', 10, 2)->nullable();

                $table->string('status', 30)->default('draft');
                $table->boolean('is_featured')->default(false);

                $table->string('meta_title', 255)->nullable();
                $table->text('meta_description')->nullable();

                $table->timestamps();
                $table->softDeletes();

                $table->index('status');
                $table->index('stock_status');
                $table->index('is_featured');
            }
        );
    }

    public function down(): void
    {
        $this->schema()->dropIfExists(
            'shopping_products'
        );
    }
}
