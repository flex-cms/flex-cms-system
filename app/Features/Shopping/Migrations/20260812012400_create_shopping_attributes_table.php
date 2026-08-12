<?php

declare(strict_types=1);

use Flex\Core\Features\Migrations\FeatureMigration;
use Illuminate\Database\Schema\Blueprint;

final class CreateShoppingAttributesTable extends FeatureMigration
{
    public function up(): void
    {
        $this->schema()->create(
            'shopping_attributes',
            static function (Blueprint $table): void {
                $table->bigIncrements('id');

                $table->string('name', 120);
                $table->string('slug', 120)->unique();
                $table->string('type', 30)->default('select');
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true);

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        $this->schema()->dropIfExists(
            'shopping_attributes'
        );
    }
}
