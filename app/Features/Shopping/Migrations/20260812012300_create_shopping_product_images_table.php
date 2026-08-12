<?php

declare(strict_types=1);

use Flex\Core\Features\Migrations\FeatureMigration;
use Illuminate\Database\Schema\Blueprint;

final class CreateShoppingProductImagesTable extends FeatureMigration
{
    public function up(): void
    {
        $this->schema()->create(
            'shopping_product_images',
            static function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('product_id');

                $table->string('path', 500);
                $table->string('alt', 255)->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_primary')->default(false);

                $table->timestamps();

                $table->index([
                    'product_id',
                    'sort_order',
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
            'shopping_product_images'
        );
    }
}
