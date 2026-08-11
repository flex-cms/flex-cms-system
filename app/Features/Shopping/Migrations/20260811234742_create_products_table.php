<?php

declare(strict_types=1);

use Flex\Core\Features\Migrations\FeatureMigration;
use Illuminate\Database\Schema\Blueprint;

final class CreateProductsTable extends FeatureMigration
{
    public function change(): void
    {
        $this->schema()->create(
            'shopping_products',
            static function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->string('name');
                $table->timestamps();
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
