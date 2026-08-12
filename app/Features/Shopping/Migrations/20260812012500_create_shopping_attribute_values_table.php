<?php

declare(strict_types=1);

use Flex\Core\Features\Migrations\FeatureMigration;
use Illuminate\Database\Schema\Blueprint;

final class CreateShoppingAttributeValuesTable extends FeatureMigration
{
    public function up(): void
    {
        $this->schema()->create(
            'shopping_attribute_values',
            static function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('attribute_id');

                $table->string('value', 190);
                $table->string('slug', 190);
                $table->unsignedInteger('sort_order')->default(0);

                $table->timestamps();

                $table->unique([
                    'attribute_id',
                    'slug',
                ]);

                $table->foreign('attribute_id')
                    ->references('id')
                    ->on('shopping_attributes')
                    ->cascadeOnDelete();
            }
        );
    }

    public function down(): void
    {
        $this->schema()->dropIfExists(
            'shopping_attribute_values'
        );
    }
}
