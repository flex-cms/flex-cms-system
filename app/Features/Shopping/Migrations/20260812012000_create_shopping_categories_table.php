<?php

declare(strict_types=1);

use Flex\Core\Features\Migrations\FeatureMigration;
use Illuminate\Database\Schema\Blueprint;

final class CreateShoppingCategoriesTable extends FeatureMigration
{
    public function up(): void
    {
        $this->schema()->create(
            'shopping_categories',
            static function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('parent_id')->nullable();

                $table->string('name', 190);
                $table->string('slug', 190)->unique();
                $table->text('description')->nullable();
                $table->string('image', 500)->nullable();

                $table->string('meta_title', 255)->nullable();
                $table->text('meta_description')->nullable();

                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);

                $table->timestamps();
                $table->softDeletes();

                $table->index('parent_id');
                $table->index(['is_active', 'sort_order']);

                $table->foreign('parent_id')
                    ->references('id')
                    ->on('shopping_categories')
                    ->nullOnDelete();
            }
        );
    }

    public function down(): void
    {
        $this->schema()->dropIfExists(
            'shopping_categories'
        );
    }
}
